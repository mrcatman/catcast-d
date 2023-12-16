import protooClient from 'protoo-client';
import * as mediasoupClient from 'mediasoup-client';
import Logger from './Logger';
const logger = new Logger('RoomClient');
const EventEmitter = require('events').EventEmitter;

function getProtooUrl(server, token, peerName, roomId, forceH264) {
  let url = `wss://conf.catcast.tv/?token=${token}&peerName=${peerName}&roomId=${roomId}`;
  if (forceH264) url = `${url}&forceH264=true`;
  return url;
}

const VIDEO_CONSTRAINTS = {
    qvga : { width: { ideal: 320 }, height: { ideal: 240 } },
    vga  : { width: { ideal: 640 }, height: { ideal: 480 } },
    hd   : { width: { ideal: 1280 }, height: { ideal: 720 } }
};

export default class RoomClient extends EventEmitter {
  constructor(
    {
      roomId,
      peerName,
      token,
      server
    }
  ) {
    super();
    this.roomId = roomId;
    this.peerName = peerName;
    this._server = server;
    this._token = token;
    this.device = mediasoupClient.getDeviceInfo();
    this._sendTransport = null;
    this._recvTransport = null;
    this._micProducer = null;
    this._webcamProducer = null;
    this._soundOnly = false;
    this._webcam = {
      device     : null,
      resolution : 'hd'
    };
    this.iceServers = [{
      urls: [
        "turn:173.194.72.127:19305?transport=udp",
        "turn:[2404:6800:4008:C01::7F]:19305?transport=udp",
        "turn:173.194.72.127:443?transport=tcp",
        "turn:[2404:6800:4008:C01::7F]:443?transport=tcp"
      ],
      username:"CKjCuLwFEgahxNRjuTAYzc/s6OMT",
      credential:"u1SQDR/SQsPQIxXNWQT7czc/G4c="
    },
      {urls:["stun:stun.l.google.com:19302"]}
      ]
    this._microphone = {
      device: null
    }
  }

  connect() {
    const protooUrl = getProtooUrl(this._server, this._token, this.peerName, this.roomId, false);
    const protooTransport = new protooClient.WebSocketTransport(protooUrl);

    this._closed = false;
    this._useSimulcast = true;
    this._peerName = this.peerName;
    this._protoo = new protooClient.Peer(protooTransport);
    this._room = new mediasoupClient.Room(
      {
        turnServers: this.iceServers,
        requestTimeout   : 30000,
        transportOptions :
          {
            udp: true,
            tcp: true
          },
        spy : this._spy
      });


    return this._join({ token: this._token, device: this.device });
  }

  close() {
    if (this._closed) return;
    this._closed = true;
    this._room.leave();
    setTimeout(() => this._protoo.close(), 250);
    this._micProducer = null;
    this._webcamProducer = null;
  }

  changeDisplayName(displayName)  {
    return this._protoo.send('change-display-name', { displayName })
      .then(() =>  {
        console.log('set name', displayName);
      })
      .catch((error) => {
        console.log(error);
      });
  }

  muteMic() {
    if (!this._micProducer) {
      return;
    }
    this._micProducer.pause();
  }

  unmuteMic() {
    if (!this._micProducer) {
      return;
    }
    this._micProducer.resume();
  }


  disableWebcam() {
    logger.debug('disableWebcam()');

    return Promise.resolve().then(() =>
      {
        this._webcamProducer.close();
     })
      .catch((error) => {
        logger.error('disableWebcam() | failed: %o', error);
      });
  }

  changeStream(newStream)  {
    try {
      const videoTrack = newStream.getVideoTracks()[0];
      if (videoTrack) {
        if (!this._webcamProducer) {
          this._webcamProducer = this._room.createProducer(videoTrack, { simulcast: true});
        }
        if (this._webcamProducer.paused) {
          this._webcamProducer.resume();
          this._webcamProducer.on('resume', () => {
             this._webcamProducer.replaceTrack(videoTrack);
          })
        } else {
          this._webcamProducer.replaceTrack(videoTrack);
        }
      } else {
        if (this._webcamProducer) {
          this._webcamProducer.pause();
        }
      }

      const audioTrack = newStream.getAudioTracks()[0];
      if (audioTrack) {
        if (!this._micProducer) {
          this._micProducer = this._room.createProducer(audioTrack, { simulcast: true});
        }
        if (this._micProducer.closed) {
          this._micProducer.resume();
          this._micProducer.on('resume', () => {
            this._micProducer.replaceTrack(audioTrack);
          })
        } else {
          this._micProducer.replaceTrack(audioTrack);
        }
      } else {
        if (this._micProducer) {
          this._micProducer.pause();
        }
      }
    } catch (e) {
      console.error('ERROR', e);
    }
  }

  changeWebcamResolution()
  {
    logger.debug('changeWebcamResolution()');

    let oldResolution;
    let newResolution;

    return Promise.resolve()
      .then(() =>
      {
        oldResolution = this._webcam.resolution;

        switch (oldResolution)
        {
          case 'qvga':
            newResolution = 'vga';
            break;
          case 'vga':
            newResolution = 'hd';
            break;
          case 'hd':
            newResolution = 'qvga';
            break;
        }

        this._webcam.resolution = newResolution;
      })
      .then(() =>
      {
        const { device, resolution } = this._webcam;

        logger.debug('changeWebcamResolution() | calling getUserMedia()');

        return navigator.mediaDevices.getUserMedia(
          {
            video :
              {
                deviceId : { exact: device.deviceId },
                ...VIDEO_CONSTRAINTS[resolution]
              }
          });
      })
      .then((stream) =>
      {
        const track = stream.getVideoTracks()[0];

        return this._webcamProducer.replaceTrack(track)
          .then((newTrack) =>
          {
            track.stop();

            return newTrack;
          });
      })
      .then((newTrack) => {
          stateActions.setWebcamInProgress(false);
      })
      .catch((error) =>
      {
        logger.error('changeWebcamResolution() failed: %o', error);

        this._webcam.resolution = oldResolution;
      });
  }

  enableAudioOnly()
  {
    logger.debug('enableAudioOnly()');

    return Promise.resolve()
      .then(() =>
      {
        if (this._webcamProducer)
          this._webcamProducer.close();

        for (const peer of this._room.peers)
        {
          for (const consumer of peer.consumers)
          {
            if (consumer.kind !== 'video')
              continue;

            consumer.pause('audio-only-mode');
          }
        }

      })
      .catch((error) =>
      {
        logger.error('enableAudioOnly() failed: %o', error);

      });
  }

  disableAudioOnly() {
    logger.debug('disableAudioOnly()');

    return Promise.resolve().then(() => {
        if (this._spy) return;

        if (!this._webcamProducer && this._room.canSend('video'))
          return this._setWebcamProducer();
      })
      .then(() => {
        for (const peer of this._room.peers) {
          for (const consumer of peer.consumers) {
            if (consumer.kind !== 'video' || !consumer.supported)
              continue;
            consumer.resume();
          }
        }
      })
      .catch((error) => {
        logger.error('disableAudioOnly() failed: %o', error);
      });
  }

  restartIce() {
    logger.debug('restartIce()');

    return Promise.resolve().then(() => {
      this._room.restartIce();
    }).catch((error) => {
      logger.error('restartIce() failed: %o', error);
    });
  }

  changeConsumerPreferredProfile(consumerId, profile) {
    logger.debug('changeConsumerPreferredProfile() [consumerId:%s, profile:%s]', consumerId, profile);

    return this._protoo.send('change-consumer-preferred-profile', {consumerId, profile}).then(() => {

    }).catch((error) => {
      logger.error('changeConsumerPreferredProfile() | failed: %o', error);
    });
  }

  requestConsumerKeyFrame(consumerId) {
    logger.debug('requestConsumerKeyFrame() [consumerId:%s]', consumerId);

    return this._protoo.send('request-consumer-keyframe', { consumerId })
      .then(() => {
      }).catch((error) => {
        logger.error('requestConsumerKeyFrame() | failed: %o', error);
      });
  }

  _join({ token, device }) {

    this._protoo.on('open', () => {
      logger.debug('protoo Peer "open" event');
      this._joinRoom({ token, device });
    });

    this._protoo.on('disconnected', () => {
      logger.warn('protoo Peer "disconnected" event');
      try { this._room.remoteClose({ cause: 'protoo disconnected' }); }
      catch (error) {}
    });

    this._protoo.on('close', () => {
      if (this._closed) return;

      logger.warn('protoo Peer "close" event');
      this.close();
    });

    this._protoo.on('notification', (notification) => {
      logger.debug('protoo "notification" event [method:%s, data:%o]', notification.method, notification.data);

      switch (notification.method)
      {
        case 'mediasoup-notification':
        {
          const mediasoupNotification = notification.data;
          this._room.receiveNotification(mediasoupNotification);
          break;
        }

        case 'active-speaker':
        {
          const { peerName } = notification.data;
          this.emit('active-speaker', {id: peerName});
          logger.debug("Active speaker", peerName);
          break;
        }

        case 'user-data':
          this.emit('user-data', notification.data);
          break;
        case 'user-left':
          this.emit('remove-peer', notification.data.id);
          break;
        case 'start-sending-media':
          this.emit('start-sending-media');
          this.startSendingMedia();
          break;
        case 'stop-sending-media':
          this.emit('stop-sending-media');
          this.stopSendingMedia();
          break;
        default: {
          logger.error('unknown protoo notification.method "%s"', notification.method);
        }
      }
    });
  }

  _joinRoom({ token, device }) {
    logger.debug('_joinRoom()');

    this._room.removeAllListeners();

    this._room.on('close', (originator, appData) => {
      if (originator === 'remote') {
        logger.warn('mediasoup Peer/Room remotely closed [appData:%o]', appData);
        return;
      }
    });

    this._room.on('request', (request, callback, errback) => {
      logger.debug('sending mediasoup request [method:%s]:%o', request.method, request);
      this._protoo.send('mediasoup-request', request).then(callback).catch((e) => {
        console.log(e);
      });
    });

    this._room.on('notify', (notification) => {
      logger.debug('sending mediasoup notification [method:%s]:%o', notification.method, notification);
      this._protoo.notify('mediasoup-notification', notification).catch((error) => {
        logger.warn('could not send mediasoup notification:%o', error);
      });
    });

    this._room.on('newpeer', (peer) =>  {
      logger.debug('room "newpeer" event [name:"%s", peer:%o]', peer.name, peer);
      this._handlePeer(peer);
    });

    this._room.join(this._peerName, { device }).then(() => {
       if (!this._spy) {
          this._sendTransport = this._room.createTransport('send', { media: 'SEND_MIC_WEBCAM' });
          this._sendTransport.on('close', (originator) => {
            logger.debug('Transport "close" event [originator:%s]', originator);
          });
       }
       this._recvTransport = this._room.createTransport('recv', { media: 'RECV' });
       this._recvTransport.on('close', (originator) => {
         logger.debug('receiving Transport "close" event [originator:%s]', originator);
       });
    }).then(async () => {
        if (this._spy) return;
        if (this._room.canSend('audio')) {
            await this._setMicProducer();
        }
        if (this._room.canSend('video') && !this._soundOnly) {
            await this._setWebcamProducer();
        }
        console.log('set all');
      }).then(() =>  {
        this.emit('join-success');
        const peers = this._room.peers;
        for (const peer of peers) {
          this._handlePeer(peer, { notify: false });
        }
        this._protoo.send('producers-ready').then(() =>  {
          console.log('producers ready event sent');
        }).catch((error) => {
          console.log(error);
        });
      }).catch((error) => {
        logger.error('_joinRoom() failed:%o', error);
        this.emit('join-error', error);
        this.close();
      });
  }

  _setMicProducer() {
    if (!this._room.canSend('audio')) {
      return Promise.reject(new Error('cannot send audio'));
    }

    if (this._micProducer) {
      return Promise.reject(new Error('mic Producer already exists'));
    }

    let producer;

    return Promise.resolve().then(() => {
        logger.debug('_setMicProducer() | calling getUserMedia()');
        if (!this._microphoneTrack) {
          return navigator.mediaDevices.getUserMedia({audio: this._microphone.device ? {deviceId: this._microphone.device.deviceId} : true});
        } else {
          return Promise.resolve(new MediaStream([this._microphoneTrack]));
        }
      }).then((stream) => {
        const track = stream.getAudioTracks()[0];
        producer = this._room.createProducer(track, null, { source: 'mic' });
        console.log('Audio producer', producer);
        //track.stop();
      }).then(() => {
        this._micProducer = producer;
        producer.on('close', (originator) => {
          logger.debug('mic Producer "close" event [originator:%s]', originator);

         // this._micProducer = null;
        });

        producer.on('pause', (originator) => {
          logger.debug('mic Producer "pause" event [originator:%s]', originator);
        });

        producer.on('resume', (originator) => {
          logger.debug('mic Producer "resume" event [originator:%s]', originator);
        });

        producer.on('handled', () => {
          logger.debug('mic Producer "handled" event');
        });

        producer.on('unhandled', () => {
          logger.debug('mic Producer "unhandled" event');
        });
      })
      .then(() => {
        logger.debug('_setMicProducer() succeeded');
      })
      .catch((error) => {
        logger.error('_setMicProducer() failed:%o', error);

        if (producer) producer.close();

        throw error;
      });
  }

  _setWebcamProducer() {
    if (!this._room.canSend('video')) {
      return Promise.reject(new Error('cannot send video'));
    }

    if (this._webcamProducer) {
      return Promise.reject(new Error('webcam Producer already exists'));
    }

    let producer;

    return Promise.resolve().then(() => {
      if (!this._webcamTrack) {
        const { device, resolution } = this._webcam;
        if (!device) throw new Error('no webcam devices');

        logger.debug('_setWebcamProducer() | calling getUserMedia()');
        return navigator.mediaDevices.getUserMedia(
          {
            video :
              {
                deviceId : { exact: device.deviceId },
                ...VIDEO_CONSTRAINTS[resolution]
              }
          });
      } else {
        return Promise.resolve(new MediaStream([this._webcamTrack]));
      }
    }).then((stream) => {
        const track = stream.getVideoTracks()[0];
        producer = this._room.createProducer(track, { simulcast: this._useSimulcast }, { source: 'webcam' });
        console.log('Video producer', producer);
       // track.stop();
    }).then(() => {
        this._webcamProducer = producer;
        const { device } = this._webcam;
        producer.on('close', (originator) => {
          logger.debug('webcam Producer "close" event [originator:%s]', originator);
          //this._webcamProducer = null;
         });

        producer.on('pause', (originator) => {
          logger.debug('webcam Producer "pause" event [originator:%s]', originator);
        });

        producer.on('resume', (originator) => {
          logger.debug('webcam Producer "resume" event [originator:%s]', originator);
         });

        producer.on('handled', () => {
          logger.debug('webcam Producer "handled" event');
        });

        producer.on('unhandled', () => {
          logger.debug('webcam Producer "unhandled" event');
        });
      })
      .then(() =>
      {
        logger.debug('_setWebcamProducer() succeeded');
      })
      .catch((error) =>
      {
        logger.error('_setWebcamProducer() failed:%o', error);
        if (producer) producer.close();

        throw error;
      });
  }

  setDevices({audioDevice, videoDevice}) {
   console.log('setDevices()', audioDevice, videoDevice);
      if (videoDevice) {
        this._webcam.device = videoDevice;
      } else {
        this._webcam.device = null;
      }
    if (audioDevice) {
      this._microphone.device = audioDevice;
    } else {
      this._microphone.device = null;
    }
  }

  _getWebcamType(device)
  {
    if (/(back|rear)/i.test(device.label))
    {
      logger.debug('_getWebcamType() | it seems to be a back camera');

      return 'back';
    }
    else
    {
      logger.debug('_getWebcamType() | it seems to be a front camera');

      return 'front';
    }
  }

  _handlePeer(peer, { notify = true } = {}){
    this.emit('add-peer', {peer});

    for (const consumer of peer.consumers)
    {
      this._handleConsumer(peer, consumer);
    }

    peer.on('close', (originator) => {
      this.emit('remove-peer', {peer});

      logger.debug('peer "close" event [name:"%s", originator:%s]', peer.name, originator);
    });

    peer.on('newconsumer', (consumer) => {
      logger.debug('peer "newconsumer" event [name:"%s", id:%s, consumer:%o]', peer.name, consumer.id, consumer);
      this._handleConsumer(peer, consumer);
    });
  }

  _handleConsumer(peer, consumer)
  {
    const codec = consumer.rtpParameters.codecs[0];
    consumer.on('close', (originator) => {
      logger.debug('consumer "close" event [id:%s, originator:%s, consumer:%o]', consumer.id, originator, consumer);
    });

    consumer.on('pause', (originator) => {
      logger.debug('consumer "pause" event [id:%s, originator:%s, consumer:%o]', consumer.id, originator, consumer);
    });

    consumer.on('resume', (originator) => {
      logger.debug('consumer "resume" event [id:%s, originator:%s, consumer:%o]', consumer.id, originator, consumer);
    });

    consumer.on('effectiveprofilechange', (profile) => {
      this.emit('effective-profile-change');
      logger.debug('consumer "effectiveprofilechange" event [id:%s, consumer:%o, profile:%s]', consumer.id, consumer, profile);
    });

    if (consumer.supported) {
     if (consumer.kind === 'video') {
       consumer.pause('audio-only-mode');
     }

      consumer.receive(this._recvTransport)
        .then((track) =>
        {
          consumer.resume();
          this.emit('add-track', {peer, track});
        })
        .catch((error) =>
        {
          logger.error('unexpected error while receiving a new Consumer:%o', error);
        });
    }
  }

  startSendingMedia() {
    if (this._webcamProducer) {
      this._webcamProducer.send(this._sendTransport);
    }
    if (this._micProducer){
      this._micProducer.send(this._sendTransport);
    }
  }

  stopSendingMedia() {
    if (this._webcamProducer) {
      this._webcamProducer.pause();
    }
    if (this._micProducer){
      this._micProducer.pause();
    }
    this.close();
  }

  setPredefinedMediaStream(stream) {
    if (!stream) {
      this._spy = true;
      return;
    }
    this._webcamTrack = stream.getVideoTracks()[0];
    this._microphoneTrack = stream.getAudioTracks()[0];
  }

  sendRequest({id}) {
     return this._protoo.send('accept-user', { id }).then(() =>  {
      console.log('request sent');
    }).catch((error) => {
      console.log(error);
    });
  }

  sendRejectRequest({id}) {
    return this._protoo.send('reject-user', { id }).then(() =>  {
      console.log('reject request sent');
    }).catch((error) => {
      console.log(error);
    });
  }

  setSoundOnly(state) {
    this._soundOnly = state;
  }

}
