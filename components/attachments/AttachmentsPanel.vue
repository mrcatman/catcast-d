<template>
  <div class="attachments-panel">
    <div class="attachments-panel__inner">
      <div class="attachments-panel__other-buttons">
        <slot name="buttons"></slot>
      </div>
      <div class="attachments-panel__buttons">
        <div class="comments-panel__attachment-buttons">
          <c-button @click="attachPhoto()" icon="photo" flat>{{$t('comments.attach_picture')}}</c-button>
          <c-button v-if="videos" @click="videoPanelVisible = true" icon="video_library" flat>{{$t('comments.attach_video')}}</c-button>
        </div>
      </div>
    </div>
    <input ref="filepicker" @change="onFileInputChange" type="file" style="display:none"/>
    <MediaSearchSelect v-if="videoPanelVisible" @close="videoPanelVisible = false" v-model="videoToAdd" :channel-id="channelId"/>
    <div class="attachments-panel__list" :class="{'attachments-panel__list--not-empty': attachments.length > 0}">
      <div :key="attachment.id" v-for="(attachment, $index) in attachments" class="attachments-panel__preview">
        <a @click="attachments.splice($index, 1)" class="attachments-panel__preview__close">
          <i class="material-icons">close</i>
        </a>
        <div class="attachments-panel__preview__error" v-if="attachment.upload_error">
          <span class="attachments-panel__preview__error__icon">
            <i class="material-icons">warning</i>
          </span>
          <span class="attachments-panel__preview__error__text">
            {{attachment.upload_error}}
          </span>
        </div>
        <div class="attachments-panel__preview__loading" v-if="attachment.loading || attachment.uploading">
          <c-preloader  />
        </div>
        <img v-if="attachment.data && attachment.data.full_url" :src="attachment.data.full_url" class="attachments-panel__preview__picture"/>
        <img v-else-if="attachment.attachment_type === 'picture'" :ref="attachment.uid" class="attachments-panel__preview__picture"/>
        <img v-else-if="attachment.attachment_type === 'video'" :src="attachment.data.thumbnail" class="attachments-panel__preview__picture"/>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .attachments-panel {
    width: 100%;

    &__inner {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    &__list {
      display: flex;
      flex-wrap: wrap;

      &--not-empty {
        margin: 1.25em 0;
        min-height: 10em;
      }
    }

    &__preview {
      position: relative;
      min-width: 2.5em;
      max-width: 25%;
      background: rgba(0, 0, 0, 0.25);
      padding: .5em .75em;
      margin: 0 .5em 0 0;
      border-radius: .5em;
      display: flex;
      align-items: center;
      justify-content: center;

      &__error {
        font-size: .75em;
        box-sizing: border-box;
        text-align: center;
        position: absolute;
        width: 100%;
        left: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        color: var(--negative-color);
        padding: .5em;
        display: flex;
        align-items: center;
      }

      &__loading {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      &__close {
        position: absolute;
        top: 1em;
        right: 1em;
        cursor: pointer;
      }

      &__picture {
        max-height: 10em;
        max-width: 100%;
      }

    }

    @media screen and (max-width: 768px) {
      &__other-buttons {
        margin: 0 0 1em;
      }
      &__inner {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  }
</style>
<script>
  import MediaSearchSelect from "@/components/MediaSearchSelect.vue";
  const maxPhotosCount = 3;

  function guid() {
    function s4() {
      return Math.floor((1 + Math.random()) * 0x10000)
        .toString(16)
        .substring(1);
    }
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
  }

  export default {
    components: {
      MediaSearchSelect,
    },
    watch: {
      value(newValue) {
        this.attachments = newValue;
      },
      attachments(newAttachments) {
        this.onChange();
      },
      videoPanelVisible(visible) {
        if (!visible) {
          if (this.videoToAdd) {
            this.attachments.push({
              attachment_type: 'video',
              attachment_id: this.videoToAdd.id,
              data: this.videoToAdd
            });
            this.videoToAdd = null;
          }
        }
      }
    },
    data() {
      return {
        attachments: this.value || [],
        videoPanelVisible: false,
        videoToAdd: null,
      }
    },
    mounted() {
      this.attachments.forEach(attachment => {
        this.$set(attachment, 'uploaded', true);
      });
      this.$parent.$on('save_attachments', () => {
        this.attachments.forEach(attachment => {
          if (attachment.upload_error) {
            attachment.upload_error = null;
          }
        });
        this.save();
      })
    },
    props: {
      channelId: Number,
      value: Array,
      videos: Boolean,
    },
    methods: {
      onChange() {
      //  this.$emit('input', this.attachments);
      },
      async save() {
        try {
          let unsavedPictures = this.attachments.filter(attachment => (attachment.attachment_type === 'picture' && !attachment.attachment_id && !attachment.upload_error));
          if (unsavedPictures.length > 0) {
            for (let picture of unsavedPictures) {
              await this.uploadPicture(picture);
           //   this.onChange();
            }
          }
          this.$emit('ready');
        } catch (e) {
          this.$emit('error');
        }
      },
      uploadPicture(picture) {
        return new Promise((resolve, reject) => {
          let fd = new FormData();
          fd.append('picture', picture.file);
          if (this.channelId) {
            fd.append('channel_id', this.channelId);
          }
          picture.uploading = true;
          this.$api.post('/upload/pictures', fd, {formData: true}).then(data => {
            this.$set(picture, 'data', {
              'id': data.id,
              'full_url': data.full_url
            });
            picture.uploaded = true;
            picture.attachment_id = data.id;
            resolve();
          }).catch(err => {
            picture.upload_error = this.$t('upload._errors.upload_error', {error: err.message ? this.$t(err.message) : err.toString()});
            reject();
          }).finally(() => {
            picture.uploading = false;
          })
        })
      },
      attachPhoto() {
        this.$refs.filepicker.click();
      },
      onFileInputChange(e) {
        let files = e.target.files;
        if (files) {
          let image = files[0];
          let re = /(?:\.([^.]+))?$/;
          let ext = re.exec(image.name)[1];
          ext = ext.toLowerCase();
          let extensions = ['png', 'jpg', 'jpeg', 'gif'];
          if (extensions.indexOf(ext) !== -1) {
            let attachment = {
              uid: guid(),
              attachment_type: 'picture',
              uploaded: false,
              upload_error: null,
              uploading: false,
              loading: true,
              file: image
            };
            this.attachments.push(attachment);
            const reader = new FileReader();
            reader.onload = () => {
              attachment.loading = false;
              this.$refs[attachment.uid][0].src = reader.result;
              this.$nextTick(() => {
                this.$emit('update');
              })
            };
            reader.readAsDataURL(image);
          }
        }
      }
    }
  }
</script>
