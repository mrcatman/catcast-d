class Source {
  constructor(args) {
    if (!args) {
      args = {};
    }
    this.disabled = !!args.disabled;
    this._inputs = [];
    this.componentName = null;
    this._data = {};
  }

  disable() {
    this.disabled = true;
  }

  enable() {
    this.disabled = false;
  }

  static get title() {
    return 'Source';
  }

  static get typeName() {
    return 'Source';
  }

  set isPreviewMode(mode) {
    this._isPreviewMode = !!mode;
  }


  get componentInstance() {
    return this._componentInstance;
  }


  set componentInstance(component) {
    this._componentInstance = component;
  }

  get inputs() {
    let inputs = JSON.parse(JSON.stringify(this._inputs));
    inputs.forEach(block => {
      block.inputs.forEach(row => {
        row.forEach(input => {
          if (input._valuesGetter) {
            input.values = this[input._valuesGetter];
          }
        })
      })
    });
    return inputs;
  }

  get inputsFlatList() {
    let inputs = [];
    this.inputs.forEach(block => {
      block.inputs.forEach(row => {
        row.forEach(input => {
          inputs.push(input);
        })
      })
    });
    return inputs;
  }

  get data() {
    return this._data;
  }

  set data(data) {
    this._data = data;
  }

  bindElement(container) {
    this._componentInstance.bindElement(container);
  }

  setDisabled(disabled) {
    if (disabled) {
      this.disable();
    } else {
      this.enable();
    }
  }

  delete() {

  }
}


class Camera extends Source {

  constructor() {
    super();

    this._videoDevices = [];
    this._audioDevices = [];

    this._inputs = [
      {
        alwaysVisible: true,
        inputs: [
          [
            {
              name: 'new_studio.camera.video_device_id',
              id: 'videoDeviceId',
              type: 'select',
              _valuesGetter: '_videoDevices'
            },
          ],
          [
            {
              name: 'new_studio.camera.audio_device_id',
              id: 'audioDeviceId',
              type: 'select',
              _valuesGetter: '_audioDevices'
            },
          ],
          [
            {
              name: 'new_studio.common_fields.x',
              id: 'x',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
            {
              name: 'new_studio.common_fields.y',
              id: 'y',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
          ],
          [
            {
              name: 'new_studio.common_fields.width',
              id: 'width',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
            {
              name: 'new_studio.common_fields.height',
              id: 'height',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
          ],
          [
            {
              name: 'new_studio.common_fields.volume',
              id: 'volume',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
          ]
        ]
      }
    ]
    this.componentName = 'CameraSource';
    this._data = {
      _fromClass: true,
      videoDeviceId: null,
      audioDeviceId: null,
      x: 0,
      y: 0,
      width: .5,
      height: .5,
      volume: 1
    };
  };


  static get title() {
    return 'new_studio.camera._title';
  }

  get videoDevices() {
    return this._videoDevices;
  }

  set componentInstance(component) {
    this._componentInstance = component;
    component.getDevicesList();
    component.$on('devices-list', ({audioDevicesList, videoDevicesList}) => {
      this._videoDevices = videoDevicesList;
      this._audioDevices = audioDevicesList;
    })
  }

  static get typeName() {
    return 'Camera';
  }

}


class Iframe extends Source {

  constructor() {
    super();

    this._inputs = [
      {
        alwaysVisible: true,
        inputs: [
          [
            {
              name: 'new_studio.iframe.src',
              id: 'src',
              type: 'input',
              inputType: 'text',
            },
          ],
          [
            {
              name: 'new_studio.iframe.show_in_preview',
              id: 'showInPreview',
              type: 'checkbox',
            }
          ],
          [
            {
              name: 'new_studio.common_fields.x',
              id: 'x',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
            {
              name: 'new_studio.common_fields.y',
              id: 'y',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
          ],
          [
            {
              name: 'new_studio.common_fields.width',
              id: 'width',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
            {
              name: 'new_studio.common_fields.height',
              id: 'height',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
          ]
        ]
      }
    ]
    this.componentName = 'IframeSource';
    this._data = {
      url: '',
      showInPreview: true,
      x: 0,
      y: 0,
      width: 1,
      height: 1,
    };
  };


  static get title() {
    return 'new_studio.iframe._title';
  }


  static get typeName() {
    return 'Iframe';
  }

}



class Text extends Source {

  constructor() {
    super();

    this._inputs = [
      {
        name: 'new_studio.text.main',
        inputs: [
          [
            {
              name: 'new_studio.common_fields.x',
              id: 'x',
              type: 'input',
              inputType: 'number',
              slider: true,
              percent: true,
              min: 0,
              max: 1
            },
            {
              name: 'new_studio.common_fields.y',
              id: 'y',
              type: 'input',
              inputType: 'number',
              slider: true,
              percent: true,
              min: 0,
              max: 1
            },
          ],
          [
            {
              name: 'new_studio.common_fields.width',
              id: 'width',
              type: 'input',
              inputType: 'number',
              slider: true,
              percent: true,
              min: 0,
              max: 1
            },
            {
              name: 'new_studio.common_fields.height',
              id: 'height',
              type: 'input',
              inputType: 'number',
              slider: true,
              percent: true,
              min: 0,
              max: 1
            },
          ],
          [
            {
              name: 'new_studio.text.color',
              id: 'color',
              type: 'color',
            },
            {
              name: 'new_studio.text.align',
              id: 'textAlign',
              type: 'select',
              _valuesGetter: '_textAligns'
            }
          ],
          [
            {
              name: 'new_studio.text.value',
              id: 'text',
              type: 'input',
              inputType: 'textarea',
            }
          ]
        ],
      },
      {
        name: 'new_studio.text.font',
        inputs: [
          [
            {
              name: 'new_studio.text.font_size',
              id: 'fontSize',
              type: 'input',
              inputType: 'number',
              min: 1,
              max: 200,
              slider: true,
            },
            {
              name: 'new_studio.text.font_name',
              id: 'fontName',
              type: 'input',
              inputType: 'text',
            },
            {
              name: 'new_studio.text.font_weight',
              id: 'fontWeight',
              type: 'select',
              _valuesGetter: '_fontWeights'
            }
          ],
        ]
      },
      {
        name: 'new_studio.text.shadow',
        inputs: [
          [
            {
              name: 'new_studio.text.shadow_on',
              id: 'shadowOn',
              type: 'checkbox',
            },
          ],
          [
            {
              name: 'new_studio.text.shadow_offset_x',
              id: 'shadowOffsetX',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: -120,
              max: 120,
            },
            {
              name: 'new_studio.text.shadow_offset_y',
              id: 'shadowOffsetY',
              type: 'input',
              inputType: 'number',
               slider: true,
              min: -120,
              max: 120
            },
          ],
          [
            {
              name: 'new_studio.text.shadow_blur',
              id: 'shadowBlur',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 100,
            },
            {
              name: 'new_studio.text.shadow_color',
              id: 'shadowColor',
              type: 'color',
            },
          ],
        ],
      }
    ];

    this.componentName = 'TextSource';
    this._data = {
      fontSize: 36,
      fontName: 'Montserrat',
      fontWeight: 300,
      color: '#fff',
      x: 0,
      y: 0,
      width: 1,
      height: 1,
      text: 'Catcast.tv',
      shadowOn: false,
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      shadowColor: '#00f',
      textAlign: 'left'
    };
    this._fontWeights = [];
    for (let i = 1; i <= 9; i++) {
      this._fontWeights.push({
        name: "" + (i * 100),
        value: i * 100
      })
    }
    this._textAligns = [
      {name: 'Left', value: 'left'},
      {name: 'Center', value: 'center'},
      {name: 'Right', value: 'right'},
    ]
  };


  static get title() {
    return 'new_studio.text._title';
  }


  static get typeName() {
    return 'Text';
  }

}

class Clock extends Text {

  constructor() {
    super();
    this._inputs[0].inputs[3][0] = {
      name: 'new_studio.clock.format',
      id: 'format',
      type: 'select',
      _valuesGetter: '_clockFormats'
    };
    this._clockFormats = [
      {
        name: 'HH:MM',
        value: 'h:i'
      },
      {
        name: 'HH:MM:SS',
        value: 'h:i:s'
      }
    ];
    this._data.format = "h:i";
    this._data.text = "";
    this.updateClock = this.updateClock.bind(this);
    if (!this.disabled) {
      this._clockInterval = setInterval(this.updateClock, 1000);
      this.updateClock();
    }
  };

  enable() {
    super.enable();
    this._clockInterval = setInterval(this.updateClock, 1000);
    this.updateClock();
  }

  disable() {
    super.disable();
    if (this._clockInterval) {
      clearInterval(this._clockInterval);
    }
  }

  updateClock() {
    let string = this._data.format;
    let now = new Date();
    let hours = now.getHours().toString().padStart(2, 0);
    let minutes = now.getMinutes().toString().padStart(2, 0);
    let seconds = now.getSeconds().toString().padStart(2, 0);
    string = string.replace("h", hours);
    string = string.replace("i", minutes);
    string = string.replace("s", seconds);
    this._data.text = string;
  }


  static get title() {
    return 'new_studio.clock._title';
  }


  static get typeName() {
    return 'Clock';
  }
}

class Picture extends Source {

  constructor() {
    super();

    this._inputs = [
      {
        alwaysVisible: true,
        inputs: [
          [
            {
              name: 'new_studio.picture.source',
              id: 'picture',
              type: 'custom',
              component: 'StudioPicturePicker'
            }
          ],
          [
            {
              name: 'new_studio.picture.keep_aspect_ratio',
              id: 'keepAspectRatio',
              type: 'checkbox',
            }
          ],
          [
            {
              name: 'new_studio.common_fields.x',
              id: 'x',
              type: 'input',
              inputType: 'number',
              slider: true,
              percent: true,
              min: 0,
              max: 1
            },
            {
              name: 'new_studio.common_fields.y',
              id: 'y',
              type: 'input',
              inputType: 'number',
              slider: true,
              percent: true,
              min: 0,
              max: 1
            },
          ],
          [
            {
              name: 'new_studio.common_fields.width',
              id: 'width',
              type: 'input',
              inputType: 'number',
              slider: true,
              percent: true,
              min: 0,
              max: 1
            },
            {
              name: 'new_studio.common_fields.height',
              id: 'height',
              type: 'input',
              inputType: 'number',
              slider: true,
              percent: true,
              min: 0,
              max: 1
            },
          ],
        ],
      }
    ];

    this.componentName = 'PictureSource';
    this._data = {
      picture: '',
      x: 0,
      y: 0,
      width: 1,
      height: 1,
      keepAspectRatio: true
    };
  };


  static get title() {
    return 'new_studio.picture._title';
  }


  static get typeName() {
    return 'Picture';
  }
}


class Conference extends Source {

  constructor() {
    super();

    this._videoDevices = [];
    this._audioDevices = [];

    this._inputs = [
      {
        alwaysVisible: true,
        inputs: [
          [
            {
              id: 'conference',
              type: 'custom',
              component: 'StudioConferenceManager',
            },
          ]
        ]
      },
      {
        alwaysVisible: false,
        name: '',
        inputs: [
          [
            {
              name: 'new_studio.camera.video_device_id',
              id: 'videoDeviceId',
              type: 'select',
              _valuesGetter: '_videoDevices'
            },
          ],
          [
            {
              name: 'new_studio.camera.audio_device_id',
              id: 'audioDeviceId',
              type: 'select',
              _valuesGetter: '_audioDevices'
            },
          ],
          [
            {
              name: 'new_studio.common_fields.x',
              id: 'x',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
            {
              name: 'new_studio.common_fields.y',
              id: 'y',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
          ],
          [
            {
              name: 'new_studio.common_fields.width',
              id: 'width',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
            {
              name: 'new_studio.common_fields.height',
              id: 'height',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
          ],
          [
            {
              name: 'new_studio.common_fields.volume',
              id: 'volume',
              type: 'input',
              inputType: 'number',
              slider: true,
              min: 0,
              max: 1,
              percent: true
            },
          ]
        ]
      }
    ];
    this.componentName = 'ConferenceSource';
    this._data = {
      _fromClass: true,
      videoDeviceId: null,
      audioDeviceId: null,
      x: 0,
      y: 0,
      width: .5,
      height: .5,
      volume: 1,
      conference: {
        id: null,
        object: null,
        peers: []
      }
    };
  };


  static get title() {
    return 'new_studio.conference._title';
  }

  get videoDevices() {
    return this._videoDevices;
  }

  set componentInstance(component) {
    this._componentInstance = component;
    component.getDevicesList();
    component.$on('devices-list', ({audioDevicesList, videoDevicesList}) => {
      this._videoDevices = videoDevicesList;
      this._audioDevices = audioDevicesList;
    })
  }

 // connect(conference) {
 //   this._componentInstance.connect(conference);
 // }

  static get typeName() {
    return 'Conference';
  }
}


let sourcesList = {
  'Camera': Camera,
  'Iframe': Iframe,
  'Text': Text,
  'Clock': Clock,
  'Picture': Picture,
  'Conference': Conference
};

export default sourcesList;
