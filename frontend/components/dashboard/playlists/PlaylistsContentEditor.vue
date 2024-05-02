<template>
  <div class="dashboard-page__playlists__content-editor">
      <c-box no-padding>
        <template slot="title">
          {{$t('dashboard.playlists.media.title')}}
        </template>
        <template slot="title_buttons">
          <c-button color="green" icon="add" @click="addMediaFromCurrentChannel()">{{$t('dashboard.playlists.media.add_from_current_channel')}}</c-button>
          <c-button icon="search" @click="searchMedia()">{{$t('dashboard.playlists.media.search')}}</c-button>
        </template>
        <template slot="main">
          <div class="dashboard-page__playlists__content-editor__items" v-dragula="media" drake="main">
            <media-manager-item v-for="(mediaItem, $index) in media" :key="mediaItem.id" :item="{object: mediaItem, is_folder: false}" :config="{disableLinks: true, disableSelection: true, disableEditing: true}">
              <template slot="custom_buttons">
                <c-button transparent icon-only icon="menu">
                  <c-popup-menu :key="mediaItem.id" position="bottom-left" activate-on-parent-click>
                    <c-popup-menu-item target="_blank" :to="`/dashboard/${channel.id}/media/${mediaItem.id}`">
                      {{ $t('dashboard.playlists.media.show') }}
                    </c-popup-menu-item>
                    <c-popup-menu-item @click="deleteItemFromPlaylist($index)">
                      {{ $t('dashboard.playlists.media.delete_from_playlist') }}
                    </c-popup-menu-item>
                  </c-popup-menu>
                </c-button>
              </template>
            </media-manager-item>
          </div>
        </template>
      </c-box>
  </div>
</template>
<style lang="scss">
.dashboard-page__playlists {
  &__content-editor {
    margin-top: -1px;
    .box__header__title {
      @media screen and (max-width: 768px) {
        display: none;
      }
    }
    &__items {
      height: calc(100vh - 24em);
      overflow: auto;
      @media screen and (max-width: 768px) {
        height: unset;
      }
    }
  }
}
</style>
<script>
import ResizableRow from '@/components/global/resizable/ResizableRow';
import ResizableRowChild from '@/components/global/resizable/ResizableRowChild';
import ResizableRowBar from '@/components/global/resizable/ResizableRowBar';
import MediaManager from "@/components/dashboard/MediaManager";
import MediaManagerItem from "@/components/dashboard/media-manager/MediaManagerItem";
import MediaSearchSelect from '@/components/MediaSearchSelect';

import {CHANNEL_TYPE_TV, MEDIA_TYPE_VIDEO, MEDIA_TYPE_AUDIO} from "@/constants/entity-types";

export default {
  watch: {
    media() {
      this.$emit('input', this.media);
    }
  },
  methods: {
    addMediaFromCurrentChannel() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        title: this.$t('dashboard.playlists.media.add_from_current_channel'),
        text: '',
        buttonColor: '',
        buttonText: this.$t('global.add'),
        component: MediaManager,
        props: {
          channel: this.channel,
          config: {
            disableLinks: true,
            disableUpload: true,
            disableEditing: true,
            disableFolderSelection: true,
            disableDiskSpaceIndicator: true,
            inModal: true,
          }
        },
        formValues: {},
        buttonDisabledFn: (mediaManagerInstance) => {
          return mediaManagerInstance ? !Object.values(mediaManagerInstance.selectedItemIds).filter(selected => !!selected).length : false;
        },
        fn: async (data, mediaManagerInstance) => {
          this.media = [
            ...(this.media || []),
            ...mediaManagerInstance.selectedItems.map(item => item.object)
          ]
        },
      })
    },
    searchMedia() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        title: this.$t('dashboard.playlists.media.search'),
        text: '',
        buttonColor: '',
        buttonText: this.$t('global.add'),
        component: MediaSearchSelect,
        props: {
          type: this.channel.type_name === CHANNEL_TYPE_TV ? MEDIA_TYPE_VIDEO : MEDIA_TYPE_AUDIO,
        },
        formValues: {},
        buttonDisabledFn: (mediaSearchInstance) => {
          return mediaSearchInstance ? !Object.values(mediaSearchInstance.selectedItemIds).filter(selected => !!selected).length : false;
        },
        fn: async (data, mediaSearchInstance) => {
          this.media = [
            ...(this.media || []),
            ...mediaSearchInstance.selectedItems
          ]
        },
      })
    },
    deleteItemFromPlaylist(index) {
      this.media.splice(index, 1);
    }
  },
  components: {
    MediaManagerItem,
    ResizableRow,
    ResizableRowChild,
    ResizableRowBar,
    MediaManager,
  },
  data() {
    return {
      media: this.playlist.media
    }
  },
  props: {
    playlist: {
      type: Object,
      required: true
    },
    channel: {
      type: Object,
      required: true
    }
  }
}
</script>
