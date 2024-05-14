<template>
  <div class="media-uploader" v-show="list.length > 0">
    <c-box no-padding>
      <template slot="title">
        {{ $t('dashboard.media.uploads_count', {total: list.length, ready: uploadedList.length}) }}
      </template>
      <template slot="title_buttons">
        <div class="buttons-row">
          <c-button transparent @click="showUploads = !showUploads" icon-only :icon="showUploads ? 'expand_more' : 'expand_less'"></c-button>
          <c-button transparent @click="close" icon-only icon="close"></c-button>
        </div>

      </template>
      <template slot="main">
        <div class="media-uploader__items" v-show="showUploads">
          <div class="media-uploader__item" :key="$index" v-for="(item, $index) in list">
            <div class="media-uploader__item__top">
              <component :is="item.id ? 'nuxt-link' : 'span'" :to="`/dashboard/${item.data.channel_id}/media/${item.uuid}`"  class="media-uploader__item__title">{{item.data.title}}</component>
              <c-button @click.native="(e) => deleteUpload(e, item)" transparent icon-only icon="close" />
            </div>

            <div v-if="item.upload_status === UploadStatuses.STATUS_ERROR" class="media-uploader__item__error">
              <c-icon icon="warning" />
              <span class="media-uploader__item__error__text">
                <c-tooltip position="top-left">{{item.error.text}}</c-tooltip>
                {{item.error.text}}
              </span>
              <c-button class="media-uploader__item__error__reupload" transparent icon-only icon="cached" @click="tryToReupload(item)" v-if="!item.error.fatal"></c-button>
            </div>
            <div v-else-if="item.upload_status === UploadStatuses.STATUS_READY" class="media-uploader__item__ready">
              <c-icon icon="done" class="media-uploader__item__ready__icon" />
              {{$t('dashboard.media.upload_ready')}}
            </div>
            <div v-else  class="media-uploader__item__upload-progress">
              <div class="media-uploader__item__upload-progress__bar">
                <div class="media-uploader__item__upload-progress__bar__text">
                  <span v-if="item.upload_status === UploadStatuses.STATUS_NOT_STARTED">{{$t('dashboard.media.waiting_for_upload')}}</span>
                  <span v-else-if="item.external">{{$t('dashboard.media.external_uploading')}}</span>
                  <span v-else-if="item.upload_status === UploadStatuses.STATUS_PROCESSING">{{$t('dashboard.media.converting')}}</span>
                  <span v-else>{{item.upload_percent}}%</span>
                </div>
                <div class="media-uploader__item__upload-progress__bar__inner" v-if="item.upload_status !==  UploadStatuses.STATUS_PROCESSING" :style="{width: item.upload_percent + '%'}"></div>
                <div class="media-uploader__item__upload-progress__bar__inner media-uploader__item__upload-progress__bar__inner--indeterminate" v-else></div>
              </div>
            </div>

          </div>
        </div>
      </template>
    </c-box>
  </div>
</template>
<script>
import { mapGetters, mapState } from 'vuex';
import tus from 'tus-js-client';

import { UploadStatuses } from '@/helpers/uploads';
import { API_URL } from "@/constants/urls";
export default  {
  data() {
    return {
      items: [],
      UploadStatuses,
      showUploads: true
    }
  },
  methods: {
    close() {
      const notUploadedCount = this.list.filter(item => item.upload_status !== UploadStatuses.STATUS_READY).length;
      if (notUploadedCount > 0) {
        this.$store.commit('modals/showStandardModal', {
          confirm: true,
          title: this.$t('dashboard.media.cancel_uploads.heading'),
          text: this.$t('dashboard.media.cancel_uploads.text'),
          buttonText: this.$t('global.cancel_action'),
          cancelText: this.$t('global.back'),
          fn: async () => {
            this.list = [];
          },
        })
      } else {
        this.list = [];
      }
    },
    async deleteUpload(e, media) {
      e.preventDefault();
      e.stopPropagation();
      let needConfirmation = false;
      try {
        if (media.upload) {
          media.upload.abort();
        }
        if (media.id) {
          if (media.upload_status === UploadStatuses.STATUS_READY) {
            needConfirmation = true;
            this.$store.commit('modals/showStandardModal', {
              confirm: true,
              title: this.$t('dashboard.media.delete.heading'),
              text: this.$t('dashboard.media.delete.text'),
              fn: async () => {
                await this.$api.delete(`/media/${media.id}`);
                this.list = this.list.filter(item => item.uuid !== media.uuid);
              },
            })
          } else {
            await this.$api.delete(`/media/${media.id}`, {noAlerts: true});
          }
        }
      } catch (e) {} finally {
        if (!needConfirmation) {
          this.list = this.list.filter(item => item.uuid !== media.uuid);
        }
      }
    },
    onMediaConverted({media}) {
      this.list = this.list.map(item => {
        if (item.id === media.id) {
          item.upload_status = UploadStatuses.STATUS_READY;
        }
        return item;
      })
    },
    onMediaConvertFail({media, error}) {
      this.list = this.list.map(item => {
       if (item.id === media.id) {
          item.error = {
            fatal: true,
            text: this.$t(error)
          };
          item.upload_status = UploadStatuses.STATUS_ERROR;
        }
        return item;
      })
    },
    tryToReupload(media) {
      media.error = null;
      this.uploadFile(media);
    },
    async uploadFile(media) {
      media.upload_status = UploadStatuses.STATUS_UPLOADING;
      try {
        if (!media.id) {
          const { id } = await this.$api.post('media', media.data);
          media.id = id;
         }
        if (media.external) {
          await this.$api.post(`/media/${media.id}/upload/external`, {url: media.data.url});
          media.upload_status = UploadStatuses.STATUS_PROCESSING;
        } else {
          const { key } = await this.$api.post(`/media/${media.id}/upload`);
          media.upload_key = key;
          this.uploadUsingTus(media);
        }
      } catch (e) {
        media.upload_status = UploadStatuses.STATUS_ERROR;
        media.error = {
          fatal: false,
          text: e.message
        }
      } finally {}
      this.startUpload();
    },
    uploadUsingTus(media) {
      media.upload = new tus.Upload(media.file, {
        endpoint: API_URL + 'files',
        retryDelays: [0, 1000, 3000, 5000, 5000, 5000, 5000, 5000, 5000, 5000, 5000, 5000],
        chunkSize: 10 * 1048576,
        metadata: {
          id: media.id,
          upload_key: media.upload_key,
          name: media.file.name,
          type: media.file.name.split('.').pop()
        },
        onError: (error) => {
          media.error = {
            fatal: false,
            text: error.toString()
          }
          media.upload_status = UploadStatuses.STATUS_ERROR;
        },
        onProgress: (bytesUploaded, bytesTotal) => {
          media.upload_percent = (bytesUploaded / bytesTotal * 100).toFixed(2)
        },
        onSuccess: async () => {
          media.upload_status = UploadStatuses.STATUS_PROCESSING;
          this.startUpload();
        }
      });
      media.upload.start();
    },
    startUpload() {
      let uploadingFilesCount = this.list.filter(file => file.upload_status === UploadStatuses.STATUS_UPLOADING).length;
      if (uploadingFilesCount < this.maxSimultaneousUploadsCount) {
        this.list.forEach(file => {
          if (file.upload_status === UploadStatuses.STATUS_NOT_STARTED && uploadingFilesCount < this.maxSimultaneousUploadsCount) {
            this.uploadFile(file);
            uploadingFilesCount++;
          }
        })
      }
    },
    startListeningToEvents() {
      this.$echo.private(`App.User.${this.user.id}`)
        .listen('.media.convert_fail', (e) => {
          this.onMediaConvertFail(e);
        })
        .listen('.media.convert_success', (e) => {
          this.onMediaConverted(e);
        })
    }
  },
  mounted() {
    this.startListeningToEvents();

    this.$store.subscribe((mutation) => {
      if (mutation.type === 'uploads/addUpload') {
        this.startUpload();
      }
    });
  },
  computed: {
    ...mapGetters('config', ['maxSimultaneousUploadsCount']),
    ...mapState('auth', ['user']),
    uploadedList() {
      return this.list.filter(item => item.upload_status === UploadStatuses.STATUS_READY);
    },
    list: {
      set(list) {
        this.$store.commit('uploads/setList', list);
      },
      get() {
        return this.$store.state.uploads.list;
      }
    },
  }
}
</script>
<style lang="scss">
.media-uploader {
  position: fixed;
  bottom: 1em;
  right: 1em;
  z-index: 10000;
  max-width: 28em;
  width: 100%;
  opacity: .5;
  font-size: .875em;
  transition: opacity .2s;
  &:hover {
    opacity: 1;
  }
  &__items {
    width: 100%;
    max-height: 20em;
    overflow: auto;
    flex: 1;
  }

  &__item {
    position: relative;

    transition: background .2s;
    text-decoration: none;
    display: block;
    padding: .75em 1em;
    border-bottom: 1px solid var(--input-border-color);
    &--link {
      cursor: pointer;
      &:hover {
        background: var(--lighten-1);
      }
    }

    &__title {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    &__top {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    &__error {
      color: var(--negative-color);
      font-weight: 400;
      display: flex;
      align-items: center;
      justify-content: flex-start;
      &__text {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin-left: .25em;
        font-size: .875em;
      }
      &__reupload {
        margin-left: auto;
      }
    }
    &__ready {
      display: flex;
      align-items: center;
      &__icon {
        color: var(--positive-color);
        margin-right: .25em;
      }
    }
    &__upload-progress {
      background: var(--darken-4);
      display: block;
      position: relative;
      height: 1.5em;
      border-radius: var(--border-radius);
      overflow: hidden;
      width: calc(100% - .75em);
      &__bar {
        height: 100%;
        &__inner {
          background: var(--active-color);
          height: 100%;
          transition: all .4s;
          position: relative;
          &--indeterminate {
            animation: indeterminateProgressBar 4s infinite forwards;
          }
        }

        &__text {
          position: absolute;
          z-index: 1;
          top: .125em;
          left: 0;
          width: 100%;
          text-align: center;
          font-weight: 500;
        }
      }
    }
  }
}
@keyframes indeterminateProgressBar {
  0% {
    width: 0;
    left: 0;
  }
  50% {
    width: 100%;
    left: 0;
  }
  100% {
    left: 100%;
  }
}
</style>
