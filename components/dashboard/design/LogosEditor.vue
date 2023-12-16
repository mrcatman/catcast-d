<template>
  <div class="logos-editor">
    <c-box>
      <template slot="title">{{ $t('dashboard.design.logos_editor._title') }}</template>
      <template slot="main">

        <div class="logos-editor__window__outer" ref="window">
          <div class="logos-editor__window__inner">
            <div class="logos-editor__window">
              <vue-draggable-resizable :w="initialPosition.width" :h="initialPosition.height" :x="initialPosition.x"
                                       :y="initialPosition.y" @dragging="onDrag" @resizing="onResize"
                                       v-if="activeLogo && activeLogo.picture  && logoVisible" :parent="true"
                                       :lock-aspect-ratio="true">
                <img class="logos-editor__active-picture" :src="activeLogo.picture.full_url"/>
              </vue-draggable-resizable>
            </div>
          </div>
        </div>

        <div class="logos-editor__list">
          <c-preloader v-if="loading"/>
          <div v-if="!loading" @click="setLogo(logo, true)"
               :style="{backgroundImage: logo.picture ? `url(${logo.picture.full_url})` : ''}"
               class="logos-editor__item" :class="{'logos-editor__item--active': logo.is_active}"
               v-for="(logo, $index) in logos" :key="$index">
            <a @click="(e) => deleteLogo(e, logo)" class="logos-editor__item__delete" v-if="!logo.is_channel_logo">
              <c-icon icon="close"/>
            </a>
          </div>
          <div v-if="!loading" @click="uploadNewLogo()" class="logos-editor__item logos-editor__item--add">
            <c-icon class="logos-editor__item--add__icon" icon="library_add"/>
          </div>
        </div>
        <c-picture-uploader ref="filepicker" @upload="onUploadPicture" style="display:none" title="" :returnData="true" folder="logos" v-model="uploadedPicture"/>
        <div class="logos-editor__save">
          <c-button :loading="saving" @click="save()">{{ $t('global.save') }}</c-button>
        </div>
      </template>
    </c-box>
  </div>
</template>
<style lang="scss">
.logos-editor {
  max-width: 66%;
  margin-top: 2em;

  &__active-picture {
    width: 100%;
    height: 100%;
  }

  &__window {
    position: relative;
    width: 100%;
    height: 100%;

    &__outer {
      position: relative;
      padding-top: 56.25%;
      background: url('/logo-preview.png') no-repeat center center;
      background-size: cover;
    }

    &__inner {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
    }
  }

  &__item {
    width: 5em;
    height: 5em;
    margin-right: 1em;
    margin-bottom: 1em;
    background-size: 75% !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
    background-color: var(--lighten-2);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: all .4s;
    position: relative;
    top: 0;

    &__delete {
      position: absolute;
      top: .25em;
      right: .25em;
      cursor: pointer;
      font-size: 1.25em;
      opacity: .5;
      transition: opacity .35s;

      &:hover {
        opacity: .85;
      }
    }

    &:hover {
      background: var(--lighten-3);
    }

    &--active, &--active:hover {
      background: var(--active-color);
    }

    &--add {
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--green);
      &:hover {
        background: var(--green);
      }
      &__icon {
        font-size: 2em;
      }
    }
  }


  &__list {
    margin: 1em 0;
    display: flex;
    flex-wrap: wrap;
  }

  &__save {
    margin-top: -1em;
  }
}
</style>
<script>
import VueDraggableResizable from 'vue-draggable-resizable'
import 'vue-draggable-resizable/dist/VueDraggableResizable.css'

export default {
  props: {
    channel: {
      type: Object,
      required: true
    },
  },
  methods: {
    save() {
      if (this.activeLogo) {
        let windowWidth = this.$refs.window.clientWidth;
        let windowHeight = this.$refs.window.clientHeight;
        let position = {
          width: Math.floor(this.position.width / windowWidth * 10000) / 10000,
          x: Math.floor(this.position.x / windowWidth * 10000) / 10000,
          y: Math.floor(this.position.y / windowHeight * 10000) / 10000,
        }
        if (!position.width) {
          position.width = 0.01;
        }
        if (!position.x) {
          position.x = 0;
        }
        if (!position.y) {
          position.y = 0;
        }
        console.log('set position', position, this.position, windowWidth, Math.floor(this.position.width / windowWidth * 10000));
        this.saving = true;
        this.$api.post(`channels/${this.channel.id}/logos/set`, {
          id: this.activeLogo.id,
          is_channel_logo: this.activeLogo.is_channel_logo,
          position
        }, {
          notifyOnSuccess: true
        }).then(logo => {
          this.logos.forEach(logoItem => {
            if (logoItem.is_channel_logo) {
              logoItem.is_channel_logo = false;
              logoItem.id = logo.id;
            }
          })
        }).finally(() => {
          this.saving = false;
        })
      } else {
        this.saving = true;
        this.$api.post(`channels/${this.channel.id}/logos/set`, {
          unset: true
        }).finally(() => {
          this.saving = false;
        })
      }
    },
    onResize(x, y, width, height) {
      this.position = {
        x,
        y,
        width,
        height
      }
    },
    onDrag(x, y) {
      this.position.x = x;
      this.position.y = y;
    },
    deleteLogo(e, logo) {
      e.stopPropagation();
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        text: null,
        fn: async () => {
          await this.$api.delete(`channels/${this.channel.id}/logos/${logo.id}`);
          this.logos.splice(this.logos.map(logoItem => logoItem.id).indexOf(logo.id), 1);
        },
      })
    },
    onUploadPicture(picture) {
      this.$api.post(`channels/${this.channel.id}/logos`, {
        picture_id: picture.id
      }).then(logo => {
        this.logos.push(logo);
      })
    },
    uploadNewLogo() {
      this.$refs.filepicker.fileInputClick();
    },
    setLogo(logo, click = false) {
      if (this.activeLogo && click && (logo.id === this.activeLogo.id || logo.is_channel_logo && this.activeLogo.is_channel_logo)) {
        this.logos.forEach(logoItem => {
          this.$set(logoItem, 'is_active', false);
        })
      } else {
        if (!logo.picture) {
          return;
        }
        this.logoVisible = false;
        let img = new Image();
        img.onload = () => {
          let windowWidth = this.$refs.window.clientWidth;
          let windowHeight = this.$refs.window.clientHeight;
          if (logo.position && logo.position.width > 0) {
            this.position = {
              width: logo.position.width * windowWidth,
              height: logo.position.width * (img.height / img.width) * windowWidth,
              x: logo.position.x * windowWidth,
              y: logo.position.y * windowHeight,
            };
            this.initialPosition = JSON.parse(JSON.stringify(this.position));
          } else {
            this.initialPosition = {
              width: 0.05 * windowWidth,
              height: 0.05 * (img.height / img.width) * windowWidth,
              x: 0.01 * windowWidth,
              y: 0.01 * windowHeight,
            }
          }
          this.logos.forEach(logoItem => {
            this.$set(logoItem, 'is_active', logoItem.id === logo.id);
          })
          this.logoVisible = true;
        }
        img.src = logo.picture.full_url;
      }
    }
  },
  computed: {
    activeLogo() {
      return this.logos.filter(logo => logo.is_active)[0];
    },
  },
  data() {
    return {
      saving: false,
      position: {},
      initialPosition: {
        width: 120,
        height: 120,
        x: 10,
        y: 10
      },
      logoVisible: false,
      uploadedPicture: null,
      logos: [],
      loading: true,
      deletePanel: {
        visible: false,
        loading: false,
        logo: null
      }
    }
  },
  async mounted() {
    this.logos = (await this.$api.get(`channels/${this.channel.id}/logos`));
    this.loading = false;
    if (this.activeLogo) {
      this.setLogo(this.activeLogo);
    }
  },
  components: {
    VueDraggableResizable
  }
}
</script>
