import * as mediasoupClient from "@/helpers/mediasoup-client";

const io = require("socket.io-client");

const EventEmitter = require('events').EventEmitter;

class StreamManager extends EventEmitter {
  constructor({wsUrl, wsQuery}) {
    super();

    this.wsUrl = wsUrl;
    this.wsQuery = wsQuery;
    this.wsConnectError = false;
    this.iceServers = [];
  }

  setMediaStream(mediaStream) {
    this.mediaStream = mediaStream;
  }

  async request(type, data = {}) {
    console.log('Making request', type, data);
    this.ws.emit("mediasoup_request", type, data);
  }

  async start() {
    this.ws = io(this.wsUrl, { query: this.wsQuery });
    console.log('ws', this.ws);
    this.ws.on("connect", () => {
      console.log('connected');
      this.request('start_mediasoup');
    });

    this.ws.on('connect_error', (e) => {
      if (!this.wsConnectError) {
        this.wsConnectError = true;
        this.emit("error", "stream_manager.errors.connect_error");
      }
    });

    this.ws.on("mediasoup_response", (type, data) => {
      console.log("Response", type, data);
      switch (type) {
        case 'rtp_capabilities':
          this.handleRtpCapabilities(data);
          break;
        case 'webrtc_transport_options':
          this.handleWebrtcTransportOptions(data);
          break;
        case 'recv_producer_ready':
          this.onRecvProducerReady(data);
          break;
        case 'fatal_error':
          this.onError(data);
          break;
        case 'started':
          this.onStarted(data);
          break;
        case 'stopped':
          this.onStopped(data);
          break;
        default:
          break;
      }
    })
  }

  onStarted(data) {
    this.emit("started");
  }

  onStopped(data) {
    this.emit("stopped");
    if (this.ws) {
      this.ws.close();
    }
  }

  onError(data) {
    this.emit("error", "stream_manager.errors." + data.code);
  }

  async handleRtpCapabilities({rtpCapabilities}) {
    let device = null;
    try {
      device = new mediasoupClient.Device();
    } catch (err) {
      this.emit("error", "stream_manager.errors.device_error");
      return;
    }
    try {
      await device.load({ routerRtpCapabilities: rtpCapabilities });
    } catch (err) {
      this.emit("error", "stream_manager.errors.device_error");
      console.error(err);
      return;
    }

    this.device = device;
    this.request('webrtc_recv_start');
  }

  async onRecvProducerReady({id}) {
    this._producerReadyCallback(id);
  }

  async handleWebrtcTransportOptions({ webrtcTransportOptions }) {
    const transportOptions = {...webrtcTransportOptions, iceServers: this.iceServers};

    let transport;
    try {
      transport = await this.device.createSendTransport(transportOptions);
    } catch (err) {
      console.error(err);
      return;
    }

    console.log(transport, transportOptions);
    transport.on("connect", ({ dtlsParameters }, callback, _errback) => {
      this.request("webrtc_recv_connect", { dtlsParameters });
      callback();
    });

    transport.on("produce", (produceParameters, callback, _errback) => {
      this.request("webrtc_recv_produce", { produceParameters });
      this._producerReadyCallback = callback;
    });

    let stream = this.mediaStream;
    const audioTrack = stream.getAudioTracks()[0];
    this.audioProducer = await transport.produce({ track: audioTrack });
    const videoTrack = stream.getVideoTracks()[0];
    this.videoProducer = await transport.produce({
      track: videoTrack,
      encodings: [
        {
          maxBitrate: 1500000
        }
      ],
      codecOptions: {
        videoGoogleStartBitrate: 1000
      }
    });
    setTimeout(() => {
      this.request("start");
    }, 3000)
  }

  stop() {
    if (this.ws) {
      this.request("stop");
    }
  }
}

export default StreamManager;
