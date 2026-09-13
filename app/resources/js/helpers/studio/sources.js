import PictureSource from "../../components/new-studio/PictureSource";

class Source {

  constructor(updateAudio = null) {
    this.uuid = Math.ceil(Math.random() * 100000);
    this.title = '';
    this.onLoadedAudio = updateAudio;
    this.video = null;
    this.audio = null;
    this.videoActive = false;
    this.audioActive = false;
    this.videoSizeX = null;
    this.videoSizeY =  null;
    this.mediaStream = null;
    this.videoSizes = [];
    this.ownVideoSizes = false;
    this.data = {};
    this.volume = 1;
  }


  getID() {
    return this.id || '';
  }

  getTitle() {
    return this.title || '';
  }

  getSourceName() {
    return this.sourceName || '';
  }

  getInputs() {
    return this.inputs || [];
  }

  init() {
    console.log('source init');
  }

  setData(data, settings) {
    this.data = data;
  }

  setSubData(data) {
    Object.keys(data).forEach(key => {
      this.data.data[key] = data[key];
    });
  }

  static get icon() {
    return null;
  }

  static get iconType() {
    return null;
  }

  setDisabled(state) {
    this.disabled = state;
  }

  setProportions(settings) {
    if (this.data.data) {
      let width = this.video.videoWidth;
      let height = this.video.videoHeight;
      let ratioX = settings.sizeX / width;
      let ratioY = settings.sizeY / width;
      if (height > settings.sizeY) {
        this.data.data.size_x = settings.sizeX;
        this.data.data.size_y = ratioY * height;
        this.data.data.x = 0;
        this.data.data.y = (settings.sizeY - this.data.data.size_y) / 2;
      } else {
        this.data.data.size_x = ratioX * width;
        this.data.data.size_y = settings.sizeY;
        this.data.data.x = (settings.sizeX - this.data.data.size_x) / 2;
        this.data.data.y = 0;
      }

    }
  }
}

class cameraSource extends Source {

  constructor(updateAudio) {
    super(updateAudio);
    this.id = 'cameraSource';
    this.title = 'studio.sources.camera.heading';
    this.canDrag = true;
    this.canResize = true;
    this.volumeNode = null;
    this.data = {
      data: {},
    };
    this.dragData = {
      position: {
        x: 0,
        y: 0
      },
      size: {
        width: 0,
        height: 0,
      },
      inputsMap: {
        x: 'x',
        y: 'y',
        width: 'size_x',
        height: 'size_y'
      }
    };
    this.inputs =  [
      {
        name: 'studio.sources._sections.volume',
        inputs: [
          [
            {
              name: 'studio.sources._fields.volume',
              id: 'volume',
              type: 'input',
              inputType: 'number',
              default: 100,
              slider: true,
              min: 1,
              max: 100,
            },
          ]
        ]
      },
      {
        name: 'studio.sources._sections.size',
        inputs: [
          [
            {
              name: 'studio.sources._fields.own_size',
              id: 'own_size',
              type: 'checkbox',
              default: false,
            },
          ],
          [
            {
              name: 'studio.sources._fields.size_x',
              id: 'size_x',
              type: 'input',
              inputType: 'number',
              default: 120,
              slider: true,
              min: 1,
              max: ({settings}) => {
                return settings.sizeX * 2;
              },
              activeIf: ({data}) => {
                return data.own_size === true;
              },
            },
            {
              name: 'studio.sources._fields.size_y',
              id: 'size_y',
              type: 'input',
              inputType: 'number',
              default: 120,
              slider: true,
              min: 1,
              max: ({settings}) => {
                return settings.sizeY * 2;
              },
              activeIf: ({data}) => {
                return data.own_size === true;
              }
            },
            {
              name: 'studio.sources._fields.x',
              id: 'x',
              type: 'input',
              inputType: 'number',
              default: 0,
              slider: true,
              min: ({settings}) => {
                return settings.sizeX * -2;
              },
              max: ({settings}) => {
                return settings.sizeX * 2;
              },
              activeIf: ({data}) => {
                return data.own_size === true;
              },
            },
            {
              name: 'studio.sources._fields.y',
              id: 'y',
              type: 'input',
              inputType: 'number',
              default: 0,
              slider: true,
              min: ({settings}) => {
                return settings.sizeY * -2;
              },
              max: ({settings}) => {
                return settings.sizeY * 2;
              },
              activeIf: ({data}) => {
                return data.own_size === true;
              }
            },
          ],
        ],
      }
    ];
  }


  addAudio(source, audioCtx) {
    let bufferLength, bufferLog, bufferSize, log10;
    bufferSize = 4096;
    bufferLength = parseFloat(bufferSize) / parseFloat(audioCtx.sampleRate);
    bufferLog = Math.log(parseFloat(bufferSize));
    log10 = 2.0 * Math.log(10);
    let controlsNode = audioCtx.createScriptProcessor(bufferSize, 2, 2);
    controlsNode.onaudioprocess = (function(_this) {
      return function(buf) {
        var channel, channelData, i, j, k, ref1, ref2, results, rms, volume;
        results = [];
        for (channel = j = 0, ref1 = buf.inputBuffer.numberOfChannels - 1; 0 <= ref1 ? j <= ref1 : j >= ref1; channel = 0 <= ref1 ? ++j : --j) {
          channelData = buf.inputBuffer.getChannelData(channel);
          rms = 0.0;
          for (i = k = 0, ref2 = channelData.length - 1; 0 <= ref2 ? k <= ref2 : k >= ref2; i = 0 <= ref2 ? ++k : --k) {
            rms += Math.pow(channelData[i], 2);
          }
          volume = 100 * Math.exp((Math.log(rms) - bufferLog) / log10);
          this.volume = volume;
          results.push(buf.outputBuffer.getChannelData(channel).set(channelData));
        }
        return results;
      };
    })(this);


    controlsNode.connect(audioCtx.destination);
    let trackGain = audioCtx.createGain();
    trackGain.connect(controlsNode);

    let passThrough;
    passThrough = audioCtx.createScriptProcessor(256, 2, 2);
    passThrough.onaudioprocess = (function(_this) {
      return function(buf) {
        let channel, channelData, j, ref, results;
        channelData = buf.inputBuffer.getChannelData(channel);

        results = [];
        for (channel = j = 0, ref = buf.inputBuffer.numberOfChannels - 1; 0 <= ref ? j <= ref : j >= ref; channel = 0 <= ref ? ++j : --j) {
          if (false) {
            results.push(buf.outputBuffer.getChannelData(channel).set(channelData));
          } else {
            results.push(buf.outputBuffer.getChannelData(channel).set(new Float32Array(channelData.length)));
          }
        }
        return results;
      };
    })(this);
    passThrough.connect(audioCtx.destination);
    trackGain.connect(passThrough);
    source.connect(trackGain);
    this.audio = trackGain;
    this.audioActive = true;
  }

  init(data, videoElement, audioCtx, settings) {
    return new Promise(async (resolve, reject) => {
      let {deviceId, deviceName, audioDeviceId, audioDeviceName} = data;
      if (deviceName && audioDeviceName) {
        this.sourceName = deviceName + " / " + audioDeviceName;
      } else {
        if (deviceName) {
          this.sourceName = deviceName;
        } else {
          if (audioDeviceName) {
            this.sourceName = audioDeviceName;
          }
        }
      }
      if (deviceId) {
        let constraints = {
          deviceId: {
            exact: deviceId
          }
        };
        let getUserMediaSettings = {
          video: constraints
        };

        data.volume = 100;
        this.data.data = data;


        let mediaStream = await navigator.mediaDevices.getUserMedia(getUserMediaSettings);
        if (videoElement) {
          this.video = videoElement;
        } else {
          this.video = document.createElement('video');
        }
        this.video.srcObject = mediaStream;
        //this.mediaStream = mediaStream;

        this.video.onloadedmetadata = (e) => {
          this.setProportions(settings);
          this.video.muted = true;
          this.video.play();
          //this.videoSizeX = this.video.videoWidth;
          //this.videoSizeY = this.video.videoHeight;
          this.videoActive = true;
        }
      }

      if (!audioDeviceId) {
        resolve();
      } else {
        let constraints = {
          deviceId: {
            exact: audioDeviceId
          }
        };
        let data = {
          audio: constraints
        };
        navigator.mediaDevices.getUserMedia(data).then(mediaStream => {
          if (audioCtx) {

            let source = audioCtx.createMediaStreamSource(mediaStream);
            // source.connect(audioCtx.destination);
            this.audio = source;
            //this.addAudio(source, audioCtx);
            this.audioActive = true;
            this.onLoadedAudio();
          }
          resolve();
        })
      }
    })
  }

  static get icon() {
    return 'camera-retro';
  }

  static get iconType() {
    return 1;
  }
}

class playlistSource extends Source {

  constructor(updateAudio) {
    super(updateAudio);
    this.id = 'playlistSource';
    this.title = 'studio.sources.playlist.heading';
    this.sourceName = 'Playlist';
    this.data = {
      data: [],
    };
    this.audio = null;
    this.playlistIndex = 0;
    this.canDrag = true;
    this.canResize = true;
    this.dragData = {
      position: {
        x: 0,
        y: 0
      },
      size: {
        width: 0,
        height: 0,
      },
      inputsMap: {
        x: 'x',
        y: 'y',
        width: 'size_x',
        height: 'size_y'
      }
    };
    this.inputs = [
      {
        name: 'studio.sources.playlist._sections.main',
        alwaysVisible: true,
        inputs: [
          [
            {
              name: 'studio.sources.playlist._fields.data',
              id: 'data',
              type: 'custom',
              component: 'playlistManager',
              default: [],
            },
          ],
        ]
      },
      {
        name: 'studio.sources._sections.size',
        inputs: [
          [
            {
              name: 'studio.sources._fields.own_size',
              id: 'own_size',
              type: 'checkbox',
              default: false,
            },
          ],
          [
            {
              name: 'studio.sources._fields.size_x',
              id: 'size_x',
              type: 'input',
              inputType: 'number',
              default: 120,
              slider: true,
              min: 1,
              max: ({settings}) => {
                return settings.sizeX * 2;
              },
              activeIf: ({data}) => {
                return data.own_size === true;
              },
            },
            {
              name: 'studio.sources._fields.size_y',
              id: 'size_y',
              type: 'input',
              inputType: 'number',
              default: 120,
              slider: true,
              min: 1,
              max: ({settings}) => {
                return settings.sizeY * 2;
              },
              activeIf: ({data}) => {
                return data.own_size === true;
              }
            },
          ],
          [
            {
              name: 'studio.sources._fields.x',
              id: 'x',
              type: 'input',
              inputType: 'number',
              default: 0,
              slider: true,
              min: ({settings}) => {
                return settings.sizeX * -2;
              },
              max: ({settings}) => {
                return settings.sizeX * 2;
              },
              activeIf: ({data}) => {
                return data.own_size === true;
              },
            },
            {
              name: 'studio.sources._fields.y',
              id: 'y',
              type: 'input',
              inputType: 'number',
              default: 0,
              slider: true,
              min: ({settings}) => {
                return settings.sizeY * -2;
              },
              max: ({settings}) => {
                return settings.sizeY * 2;
              },
              activeIf: ({data}) => {
                return data.own_size === true;
              }
            },
          ]
        ],
      }
    ];
  }


  init({data}, videoElement, audioCtx, settings) {
    return new Promise((resolve, reject) => {
      if (data) {
        this.data.data = data;
        this.video = document.createElement('video');
        this.video.crossOrigin = "anonymous";
        if (audioCtx) {
          this.audioCtx = audioCtx;
        }
        this.video.onloadedmetadata = (e) => {
          this.setProportions(settings);
        };
        resolve();
      } else {
        resolve();
      }
    })
  }

  static get icon() {
    return 'th-list';
  }

  static get iconType() {
    return 1;
  }
}

class videoConferenceSource extends Source {

  constructor(updateAudio) {
    super(updateAudio);
    this.id = 'videoConferenceSource';
    this.title = 'studio.sources.video_conference.heading';
    this.sourceName = 'VideoConference';
    this.multipleSources = true;
    this.videos = [];
    this.audios = [];
    this.data = {
      data: {},
    };
    this.audioCtx = null;
    this.inputs = [
      {
        name: 'studio.sources.video_conference._sections.main',
        alwaysVisible: true,
        inputs: [
          [
            {
              name: 'studio.sources.playlist._fields.data',
              id: 'data',
              type: 'custom',
              component: 'videoConferenceManager',
              default: [],
            },
          ],
        ]
      }
    ];
  }


  async init(data, videoElement, audioCtx, settings) {
    return new Promise(async(resolve, reject) => {
      this.data.settings = settings;
      this.data.data = data;
      this.video = document.createElement('video');
      this.video.crossOrigin = "anonymous";
      if (audioCtx) {
        this.audioCtx = audioCtx;
      }
      let {ownCamera, deviceId, deviceName, audioDeviceId, audioDeviceName} = data;
      if (ownCamera) {
        if (deviceName && audioDeviceName) {
          this.sourceName = deviceName + " / " + audioDeviceName;
        } else {
          if (deviceName) {
            this.sourceName = deviceName;
          } else {
            if (audioDeviceName) {
              this.sourceName = audioDeviceName;
            }
          }
        }
        if (deviceId) {
          let constraints = {
            deviceId: {
              exact: deviceId
            }
          };
          let getUserMediaSettings = {
            video: constraints
          };

          data.volume = 100;
          this.data.data = data;

          let video;
          let mediaStream = await navigator.mediaDevices.getUserMedia(getUserMediaSettings);
          if (videoElement) {
            video = videoElement;
          } else {
            video = document.createElement('video');
          }
          video.srcObject = mediaStream;
          this.mediaStream = mediaStream;
          video.onloadedmetadata = (e) => {
            //video.muted = true;
            //this.setProportions(settings);
            video.play();
            this.videos.push(video);
            this.videoActive = true;
            if (!audioDeviceId) {
              resolve();
            } else {
              let constraints = {
                deviceId: {
                  exact: audioDeviceId
                }
              };
              let data = {
                audio: constraints
              };
              navigator.mediaDevices.getUserMedia(data).then(audioStream => {
                this.mediaStream = new MediaStream([...this.mediaStream.getTracks(), ...audioStream.getTracks()]);
                if (audioCtx) {
                  let source = audioCtx.createMediaStreamSource(audioStream);
                  // source.connect(audioCtx.destination);
                  this.audios.push(source);
                  //this.addAudio(source, audioCtx);
                  this.audioActive = true;
                }
                resolve(true);
              })
            }
          }
        }
      } else {
        resolve();
      }
    })
  }

  static get icon() {
    return 'users';
  }

  static get iconType() {
    return 1;
  }
}

class screenCaptureSource extends Source {

  constructor() {
    super();
    this.id = 'screenCaptureSource';
    this.title = 'studio.sources.screen_capture.heading';
    this.sourceName = 'ScreenCapture';
    this.data = {
      data: {},
    };
    this.audioCtx = null;
    this.inputs = [
      {
        name: 'studio.sources._sections.size',
        alwaysVisible: true,
        inputs: [
          [
            {
              name: 'studio.sources._fields.own_size',
              id: 'own_size',
              type: 'checkbox',
              default: false,
            },
          ],
          [

            {
              name: 'studio.sources._fields.size_x',
              id: 'size_x',
              type: 'input',
              inputType: 'number',
              default: 120,
              slider: true,
              min: 1,
              max: ({settings}) => {
                return settings.sizeX * 2;
              },
              activeIf: ({data}) => {
                return data.own_size === true;
              },
            },
            {
              name: 'studio.sources._fields.size_y',
              id: 'size_y',
              type: 'input',
              inputType: 'number',
              default: 120,
              slider: true,
              min: 1,
              max: ({settings}) => {
                return settings.sizeY * 2;
              },
              activeIf: ({data}) => {
                return data.own_size === true;
              }
            },
            {
              name: 'studio.sources._fields.x',
              id: 'x',
              type: 'input',
              inputType: 'number',
              default: 0,
              slider: true,
              min: ({settings}) => {
                return settings.sizeX * -2;
              },
              max: ({settings}) => {
                return settings.sizeX * 2;
              },
              activeIf: ({data}) => {
                return data.own_size === true;
              },
            },
            {
              name: 'studio.sources._fields.y',
              id: 'y',
              type: 'input',
              inputType: 'number',
              default: 0,
              slider: true,
              min: ({settings}) => {
                return settings.sizeY * -2;
              },
              max: ({settings}) => {
                return settings.sizeY * 2;
              },
              activeIf: ({data}) => {
                return data.own_size === true;
              }
            },
          ],
        ],
      }
    ];
  }


  init(data, videoElement, audioCtx, settings) {
    return new Promise((resolve, reject) => {
      this.data.data = data;
      //
      //  this.video.crossOrigin = "anonymous";
      const gdmOptions = {
        video: {
          cursor: data.showCursor ? "always" : "never"
        },
        //audio: !!data.captureAudio
      };
      navigator.mediaDevices.getDisplayMedia(gdmOptions).then(stream => {
        this.video = document.createElement('video');
        this.video.srcObject = stream;
        this.video.onloadedmetadata = (e) => {
          this.setProportions(settings);
        };
        this.video.play();
        this.videoActive = true;
        resolve();
      });

    })
  }

  static get icon() {
    return 'desktop';
  }

  static get iconType() {
    return 1;
  }
}

let sourcesList = {
  'cameraSource': cameraSource,
  'playlistSource': playlistSource,
  'videoConferenceSource': videoConferenceSource,
  'screenCaptureSource': screenCaptureSource,

};

export default sourcesList;
