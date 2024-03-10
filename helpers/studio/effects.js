class Effect {
  constructor() {
    this.disabled = false;
    this.data = {};
    this.is_preview = false;
  }

  getID() {
    return this.id || '';
  }

  getTitle() {
    return this.title || '';
  }

  getDescription() {
    return this.description || '';
  }

  getInputs() {
    return this.inputs || [];
  }

  run() {
    console.log('run test effect');
  }

  setData(data, settings) {
    this.data = data;
  }

  setDisabled(state) {
    this.disabled = state;
  }
}

class simplePicture extends Effect {
  constructor() {
    super();
    this.id = 'simplePicture';
    this.title = 'studio.effects.simplepicture.heading';
    this.description = 'studio.effects.simplepicture.description';
    this.picture = new Image();
    this.pictureLoaded = false;
    this._ctx = null;
    this._opacity = 0;
    this.canDrag = true;
    this.canResize = true;
    this.canHandleDisabled = true;
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
        name: 'studio.effects.simplepicture._sections.main',
        inputs: [
          [
            {
              name: 'studio.effects.simplepicture._fields.picture',
              id: 'picture',
              type: 'picture',
              default: null,
            },
          ],
          [
            {
              name: 'studio.effects.simplepicture._fields.x',
              id: 'x',
              type: 'input',
              inputType: 'number',
              default: 50,
              slider: true,
              min: ({settings}) => {
                return settings.sizeX * -1;
              },
              max: ({settings}) => {
                return settings.sizeX;
              }
            },
            {
              name: 'studio.effects.simplepicture._fields.y',
              id: 'y',
              type: 'input',
              inputType: 'number',
              default: 50,
              slider: true,
              min: ({settings}) => {
                return settings.sizeY * -1;
              },
              max: ({settings}) => {
                return settings.sizeY;
              }
            }
          ]
        ]
      },
      {
        name: 'studio.effects.simplepicture._sections.size',
        inputs: [
          [
            {
              name: 'studio.effects.simplepicture._fields.own_size',
              id: 'own_size',
              type: 'checkbox',
              default: false,
            },
          ],
          [
            {
              name: 'studio.effects.simplepicture._fields.size_x',
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
              name: 'studio.effects.simplepicture._fields.size_y',
              id: 'size_y',
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
              }
            },
          ],
        ]
      }
    ];
  }

  setData(_data) {
    if (_data.picture && _data.picture.path) {
      if (!this.data || !this.data.picture || !this.data.picture.path || this.data.picture.path !== _data.picture.path) {
        this.picture.crossOrigin = "Anonymous";
        this.picture.src = _data.picture.path;
        this.pictureLoaded = false;
        this.picture.onload = () => {
          if (!_data.own_size) {
            this.dragData.size.width = this.picture.width;
            this.dragData.size.height = this.picture.height;
          }
          this.pictureLoaded = true;
          if (this._ctx) {
            this.run(this._ctx);
          }
        }
      }
    }
    if (_data.own_size) {
      this.dragData.size.width = _data.size_x;
      this.dragData.size.height = _data.size_y;
    }
    this.data = _data;
  }

  run(ctx) {
    this._ctx = ctx;
    if (this.pictureLoaded) {
      const speed = 0.05;
      if (!this.is_preview) {
        if (this.disabled && this._opacity > 0) {
          this._opacity -= speed;
          if (this._opacity < 0) {
            this._opacity = 0;
          }
        } else {
          if (!this.disabled && this._opacity < 1) {
            this._opacity += speed;
          }
        }
        ctx.globalAlpha = this._opacity;
      } else {
        ctx.globalAlpha = 1;
      }

      this.dragData.position.x = this.data.x;
      this.dragData.position.y = this.data.y;
      if (this.data.own_size) {
        ctx.drawImage(this.picture, this.data.x, this.data.y, this.data.size_x, this.data.size_y);
      } else {
        ctx.drawImage(this.picture, this.data.x, this.data.y);
      }
      ctx.globalAlpha = 1;
    }
  }

  static get icon() {
    return 'images';
  }

  static get iconType() {
    return 1;
  }
}

class simpleText extends Effect {
  constructor() {
    super();
    this.id = 'simpleText';
    this.title = 'studio.effects.simpletext.heading';
    this.description = 'studio.effects.simpletext.description';
    this.oldData = null;
    this.canDrag = true;
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
        y: 'y'
      }
    };

    this.inputs = [
      {
        name: 'studio.effects.simpletext._sections.main',
        inputs: [
          [
            {
              name: 'studio.effects.simpletext._fields.x',
              id: 'x',
              type: 'input',
              inputType: 'number',
              default: 50,
              slider: true,
              min: ({settings}) => {
                return settings.sizeX * -1;
              },
              max: ({settings}) => {
                return settings.sizeX;
              }
            },
            {
              name: 'studio.effects.simpletext._fields.y',
              id: 'y',
              type: 'input',
              inputType: 'number',
              default: 50,
              slider: true,
              min: ({settings}) => {
                return settings.sizeY * -1;
              },
              max: ({settings}) => {
                return settings.sizeY;
              }
            },
            {
              name: 'studio.effects.simpletext._fields.color',
              id: 'color',
              type: 'color',
              default: '#00f',
            },
          ],
          [
            {
              name: 'studio.effects.simpletext._fields.text',
              id: 'text',
              type: 'input',
              inputType: 'text',
              default: 'HelloWorld',
            }
          ]
        ],
      },
      {
        name: 'studio.effects.simpletext._sections.font',
        inputs: [
          [
            {
              name: 'studio.effects.simpletext._fields.font_size',
              id: 'font_size',
              type: 'input',
              inputType: 'number',
              default: 36,
              min: 1,
              max: 144,
              slider: true,
            },
            {
              name: 'studio.effects.simpletext._fields.font_name',
              id: 'font_name',
              type: 'input',
              inputType: 'text',
              default: 'Montserrat',
            }
          ],
        ]
      },
      {
        name: 'studio.effects.simpletext._sections.shadow',
        inputs: [
          [
            {
              name: 'studio.effects.simpletext._fields.shadow_on',
              id: 'shadow_on',
              type: 'checkbox',
              default: false,
            },
          ],
          [
            {
              name: 'studio.effects.simpletext._fields.shadow_offset_x',
              id: 'shadow_offset_x',
              type: 'input',
              inputType: 'number',
              default: 1,
              slider: true,
              min: -120,
              max: 120,
            },
            {
              name: 'studio.effects.simpletext._fields.shadow_offset_y',
              id: 'shadow_offset_y',
              type: 'input',
              inputType: 'number',
              default: 1,
              slider: true,
              min: -120,
              max: 120
            },
            {
              name: 'studio.effects.simpletext._fields.shadow_blur',
              id: 'shadow_blur',
              type: 'input',
              inputType: 'number',
              default: 10,
              slider: true,
              min: 0,
              max: 100,
            },
            {
              name: 'studio.effects.simpletext._fields.shadow_color',
              id: 'shadow_color',
              type: 'color',
              default: '#0f0',
            },
          ],

        ],
      },
    ]
  }

  run(ctx) {
    if (this.data) {
      ctx.font = this.data.font_size+"px "+this.data.font_name;
      ctx.fillStyle = this.data.color;
      if (this.data.shadow_on) {
        ctx.shadowOffsetX = this.data.shadow_offset_x;
        ctx.shadowOffsetY = this.data.shadow_offset_y;
        ctx.shadowBlur = this.data.shadow_blur;
        ctx.shadowColor = this.data.shadow_color;
      }
      if (!this.oldData || this.oldData.text !== this.data.text) {
        this.dragData.size.width =  ctx.measureText(this.data.text).width;
        this.dragData.size.height = this.data.font_size;
      }
      this.dragData.position.x = this.data.x;
      this.dragData.position.y = this.data.y;
      ctx.fillText(this.text || this.data.text, this.data.x, this.data.y + this.data.font_size);
      ctx.shadowOffsetX = 0;
      ctx.shadowOffsetY = 0;
      ctx.shadowBlur = 0;
      this.oldData = this.data;
    }
  }

  static get icon() {
    return 'font';
  }

  static get iconType() {
    return 1;
  }
}

class simpleClock extends simpleText {
  constructor() {
    super();
    this.id = 'simpleClock';
    this.title = 'studio.effects.simpleclock.heading';
    this.description = 'studio.effects.simpleclock.description';
    this.started = false;
    this.inputs = this.inputs.map(section => {
      section.inputs = section.inputs.map(row => {
        return row.filter(item => {
          return item.id !== 'text';
        })
      })
      return section;
    })
  }

  setClock() {
    let date = new Date();
    let h = date.getHours();
    if (h < 10) {
      h = "0" + h;
    }
    let m = date.getMinutes();
    if (m < 10) {
      m = "0" + m;
    }
    let s = date.getSeconds();
    if (s < 10) {
      s = "0" + s;
    }
    this.text = h+":"+m+":"+s;
  }

  run(ctx) {
    if (!this.started) {
      this.started = true;
      this.setClock();
      setInterval(() => {
        this.setClock();
      }, 1000);
    }
    super.run(ctx);
  }

  static get icon() {
    return 'clock';
  }

  static get iconType() {
    return 1;
  }
}

class simpleFilters extends Effect {
  constructor() {
    super();
    this.id = 'simpleFilters';
    this.title = 'studio.effects.simplefilters.heading';
    this.description = 'studio.effects.simplefilters.description';
    this.inputs = [
      [
        {
          name: 'studio.effects.simplefilters._fields.x',
          id: 'x',
          type: 'input',
          inputType: 'number',
          default: 50,
          slider: true,
          min: ({settings}) => {
            return settings.sizeX * -1;
          },
          max: ({settings}) => {
            return settings.sizeX;
          }
        },
      ]
    ]
  }

  run(ctx) {
    //let filtered = filters.filterImage(filters.verticalFlip, ctx);
    let filtered = filters.filterImage(filters.sobel, ctx);
    ctx.putImageData(filtered, 0, 0);
  }

  static get icon() {
    return 'adjust';
  }

  static get iconType() {
    return 1;
  }
}

class simpleMarquee extends Effect {
  constructor() {
    super();
    this.id = 'simpleMarquee';
    this.title = 'studio.effects.simplemarquee.heading';
    this.description = 'studio.effects.simplemarquee.description';
    this.oldData = null;
    this.xPosition = 0;
    this.textWidth = 100;
    this.canvasWidth = 0;

    this.inputs = [
      {
        name: 'studio.effects.simplemarquee._sections.main',
        inputs: [
          [
            {
              name: 'studio.effects.simplemarquee._fields.text',
              id: 'text',
              type: 'input',
              inputType: 'text',
              default: 'The quick brown fox jumps over the lazy dog',
            },
          ],
          [
            {
              name: 'studio.effects.simplemarquee._fields.y',
              id: 'y',
              type: 'input',
              inputType: 'number',
              default: ({settings}) => {
                return settings.sizeY - 60;
              },
              slider: true,
              min: ({settings}) => {
                return settings.sizeY * -1;
              },
              max: ({settings}) => {
                return settings.sizeY;
              }
            },
            {
              name: 'studio.effects.simplemarquee._fields.speed',
              id: 'speed',
              type: 'input',
              inputType: 'number',
              default: 30,
              slider: true,
              min: 0,
              max: 100,
            },
            {
              name: 'studio.effects.simplemarquee._fields.color',
              id: 'color',
              type: 'color',
              default: '#777',
            },
          ],
          [
            {
              name: 'studio.effects.simplemarquee._fields.show_background',
              id: 'show_background',
              type: 'checkbox',
              default: false,
            },
            {
              name: 'studio.effects.simplemarquee._fields.background',
              id: 'background',
              type: 'color',
              default: '#fff',
              activeIf: ({data}) => {
                return data.show_background === true;
              },
            },
            {
              name: 'studio.effects.simplemarquee._fields.background_opacity',
              id: 'background_opacity',
              type: 'input',
              inputType: 'number',
              default: 85,
              slider: true,
              min: 0,
              max: 100,
            },
          ]
        ],
      },
      {
        name: 'studio.effects.simplemarquee._sections.font',
        inputs: [
          [
            {
              name: 'studio.effects.simplemarquee._fields.font_size',
              id: 'font_size',
              type: 'input',
              inputType: 'number',
              default: 24,
              min: 1,
              max: 144,
              slider: true,
            },
            {
              name: 'studio.effects.simplemarquee._fields.font_name',
              id: 'font_name',
              type: 'input',
              inputType: 'text',
              default: 'Montserrat',
            }
          ],
        ]
      },
      {
        name: 'studio.effects.simplemarquee._sections.shadow',
        inputs: [
          [
            {
              name: 'studio.effects.simplemarquee._fields.shadow_on',
              id: 'shadow_on',
              type: 'checkbox',
              default: false,
            },
          ],
          [
            {
              name: 'studio.effects.simplemarquee._fields.shadow_offset_x',
              id: 'shadow_offset_x',
              type: 'input',
              inputType: 'number',
              default: 1,
              slider: true,
              min: -120,
              max: 120,
            },
            {
              name: 'studio.effects.simplemarquee._fields.shadow_offset_y',
              id: 'shadow_offset_y',
              type: 'input',
              inputType: 'number',
              default: 1,
              slider: true,
              min: -120,
              max: 120
            },
            {
              name: 'studio.effects.simplemarquee._fields.shadow_blur',
              id: 'shadow_blur',
              type: 'input',
              inputType: 'number',
              default: 10,
              slider: true,
              min: 0,
              max: 100,
            },
            {
              name: 'studio.effects.simplemarquee._fields.shadow_color',
              id: 'shadow_color',
              type: 'color',
              default: '#0f0',
            },
          ],

        ],
      },
    ]
  }

  setData(_data, settings) {
    this.xPosition = settings.sizeX;
    this.canvasWidth = settings.sizeX;
    this.data = _data;
  }

  run(ctx) {
    if (this.data) {
      if (this.data.show_background) {
        ctx.globalAlpha = this.data.background_opacity / 100;
        ctx.fillStyle = this.data.background;
        ctx.fillRect(0, this.data.y, this.canvasWidth,  this.data.font_size + 10);
        ctx.globalAlpha = 1;
      }
      ctx.font = this.data.font_size+"px "+this.data.font_name;
      ctx.fillStyle = this.data.color;
      if (this.data.shadow_on) {
        ctx.shadowOffsetX = this.data.shadow_offset_x;
        ctx.shadowOffsetY = this.data.shadow_offset_y;
        ctx.shadowBlur = this.data.shadow_blur;
        ctx.shadowColor = this.data.shadow_color;
      }
      if (!this.oldData || this.oldData.text !== this.data.text || this.oldData.font_size !== this.data.font_size || this.oldData.font_name !== this.data.font_name) {
        this.textWidth =  ctx.measureText(this.data.text).width;
      }

      ctx.fillText(this.data.text, this.xPosition, this.data.y + this.data.font_size);
      ctx.shadowColor = "transparent";
      if (this.xPosition > (this.textWidth * -1)) {
        this.xPosition -= this.data.speed / 10;
      } else {
        this.xPosition = this.canvasWidth;
      }

      this.oldData = this.data;
    }
  }

  static get icon() {
    return 'text-width';
  }

  static get iconType() {
    return 1;
  }
}

let effectsList = {
  'simpleText': simpleText,
  'simpleClock': simpleClock,
  'simplePicture': simplePicture,
  'simpleMarquee': simpleMarquee,
  /* 'simpleFilters': simpleFilters, */
};

export default effectsList;
