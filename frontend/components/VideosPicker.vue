<template>
  <div class="videos-picker" ref="picker">
    <div class="videos-picker__inner">
      <div class="videos-picker__panel videos-picker__panel--files">
        <c-preloader block  v-if="loading" />
        <div v-if="folders.length > 0"  class="videos-picker__item videos-picker__item--folder" @click="goBack()">
          <div class="videos-picker__item__inner">
            <div class="videos-picker__item__icon">
              <i class="material-icons">folder_open</i>
            </div>
            <div class="videos-picker__item__text">
              {{$t('playlistpicker.go_back')}}
            </div>
          </div>
        </div>

        <div class="videos-picker__draggable-container videos-picker__draggable-container--select" drake="playlist" v-dragula="getItems">
          <div class="videos-picker__item-container" :data-info="JSON.stringify(item.stringable)" :key="$index" v-for="(item, $index) in getItems">

            <div class="videos-picker__item videos-picker__item--folder" v-if="item.is_folder">
              <div class="videos-picker__item__inner">
                <div class="videos-picker__item__icon" @click="goToFolder(item)">
                  <i class="material-icons">folder_open</i>
                </div>
                <div class="videos-picker__item__text">
                  <span class="videos-picker__item__title" @click="goToFolder(item)">{{item.object.title}}</span>
                  <span class="videos-picker__item__info">
                    <span class="videos-picker__item__buttons">
                      <c-button @click="addItem(item.object)" flat icon="chevron_right" >{{$t('global.add')}}</c-button>
                    </span>
                  </span>
                </div>
              </div>
            </div>

            <div class="videos-picker__item videos-picker__item--video" v-else>
              <div class="videos-picker__item__inner">
                <div class="videos-picker__item__thumbnail" :style="`background:url('${item.object.thumbnail}') no-repeat center center; background-size: cover;`"></div>
                <div class="videos-picker__item__text">
                  <span class="videos-picker__item__title">{{item.object.title}}</span>
                  <span class="videos-picker__item__info">
                    <span class="videos-picker__item__buttons">
                      <c-button @click="addItem(item.object)" flat icon="chevron_right" >{{$t('global.add')}}</c-button>
                    </span>
                  </span>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="videos-picker__panel videos-picker__panel--selected" ref="selected_panel">
        <div class="videos-picker__draggable-container videos-picker__draggable-container--selected" :class="{'videos-picker__draggable-container--empty': selectedItems.length === 0}" v-dragula="selectedItems" drake="playlist">
          <div v-if="item !== null && item !== undefined" class="videos-picker__item videos-picker__item--video" :key="item.id+'_selected_'+$index" v-for="(item, $index) in selectedItems">
            <div class="videos-picker__item__inner">
              <div class="videos-picker__item__text">
                <span class="videos-picker__item__title">{{item.title}}</span>
                <div class="videos-picker__item__info" v-if="item.is_folder">
                  <c-tag >{{$t('scheduler.playlists.is_folder')}}</c-tag>
                </div>
              </div>
              <div class="videos-picker__item__right">
                <a @click="deleteItemFromPlaylist(item)" class="videos-picker__item__button">
                  <i class="fa fa-times"></i>
                </a>
              </div>
            </div>
            <div :ref="`folder${item.video_folder_id}`" class="videos-picker__item__folder-contents" v-if="item && item.is_folder" v-dragula="item.items" drake="playlist">
              <div v-if="folderItem" :key="folderItem.video_id ? folderItem.video_id+'_'+$index2 : folderItem.id+'_'+$index2" v-for="(folderItem, $index2) in item.items" class="videos-picker__item">
                <div class="videos-picker__item__inner">
                  <div class="videos-picker__item__text">
                    <span class="videos-picker__item__title">{{folderItem.title}}</span>
                    <div class="videos-picker__item__info">

                    </div>
                  </div>
                  <div class="videos-picker__item__right">
                    <a @click="deleteItemFromPlaylist(folderItem, $index)" class="videos-picker__item__button">
                      <i class="fa fa-times"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="videos-picker__bottom">
      <c-button @click="save()">{{$t('global.save')}}</c-button>
      <div class="videos-picker__total-length">{{$t('scheduler.playlists.total_length')}} <strong>{{totalLength}}</strong></div>
    </div>
  </div>
</template>
<script>
  import VideoMetadataEditor from '@/components/scheduler/VideoMetadataEditor';
  import { formatDuration } from '@/helpers/dates";
  export default {
    components: {
      VideoMetadataEditor
    },
    computed: {

      getItems() {
        let data = this.data;
        let items = [];
        data.playlists.forEach(project => {
          project._type = 'playlists';
          project.stringable = {
            is_folder: true,
            is_project: true,
            video_folder_id: -1 * project.id,
            title: project.name,
          };
          items.push(project);
        });
        data.folders.forEach(folder => {
          folder._type = 'folders';
          folder.stringable = {
            is_folder: true,
            is_project: false,
            video_folder_id: folder.id,
            title: folder.title,
          };
          items.push(folder);
        });
        data.videos.forEach(video => {
          if (!this.filterServerOnly || video.is_on_server) {
            video._type = 'videos';
            video.stringable = {
              video_id: video.id,
              title: video.title,
              length: video.length
            };
            items.push(video);
          }
        });
        return items;
      }
    },
    data() {
      return {
        selectedItems: this.value,
        currentProjectId: null,
        currentFolderId: null,
        folders: [],
        data: {
          videos: [],
          folders: [],
          playlists: [],
        },
        loading: true,
      }
    },
    beforeDestroy() {
      if (this.$dragula.$service.drakes.playlist) {
        delete this.$dragula.$service.drakes.playlist;
      }
    },
    watch: {
      selectedItems(newList) {
        this.$emit('input', newList);
      }
    },
    methods: {
      addItem(item, index = null) {
        console.log('add video', item);
        this.selectedItems = this.selectedItems.filter(item => (item));

        if (index) {
          this.selectedItems.splice(index, 0, video);
        } else {
          this.selectedItems.push(video);
          this.$nextTick(() => {
            let panel = this.$refs.selected_panel;
            panel.scrollTop = panel.scrollHeight;
          })
        }
      },
      save() {
        this.$emit('save', this.selectedItems);
      },
      deleteItemFromPlaylist(item, folderIndex) {
        if (folderIndex) {
          this.selectedItems[folderIndex].splice(this.selectedItems[folderIndex].indexOf(item), 1);
        }
        this.selectedItems.splice(this.selectedItems.indexOf(item), 1);
      },


      savePlaylistItem(data) {
        this.$set(this.selectedItems, this.playlistItemEditPanel.editIndex,  data);
        this.playlistItemEditPanel.visible = false;
      },
      openPlaylistItemEditPanel(item) {
        this.playlistItemEditPanel.editIndex = this.selectedItems.indexOf(item);
        this.playlistItemEditPanel.data = JSON.parse(JSON.stringify(item));
        this.playlistItemEditPanel.visible = true;
      },
      formatDuration,
      goBack() {
        this.folders.splice(this.folders.length - 1, 1);
        if (this.folders.length > 0) {
          let folder = this.folders[this.folders.length - 1];
          if (folder.is_folder) {
            this.currentProjectId = null;
            this.currentFolderId = folder.id;
          } else {
            this.currentFolderId = null;
            this.currentProjectId = folder.id;
          }
        } else {
          this.currentProjectId = null;
          this.currentFolderId = null;
        }
        this.load();
      },
      goToFolder(folder) {
        this.currentFolderId = folder.id;
        this.folders.push({
          id: folder.id,
          title: folder.title,
          is_project: false,
        });
        this.load();
      },
      load() {
        let data = {};
        if (this.currentFolderId > 0) {
          data.folder_id = this.currentFolderId;
        }
        this.loading = true;
        this.$api.get(`media/file-manager/${this.channel.id}`, data).then (res => {
          this.data = res.data.data;
          this.loading = false;
        })
      },

    },
    mounted() {
      this.load();
    },
    created() {
      const service = this.$dragula.$service;
      service.eventBus.$on('drop', (args) => {
        const sourceIsMediaList = args.source.classList.contains('videos-picker__draggable-container--select');
        const targetIsMediaList = args.container.classList.contains('videos-picker__draggable-container--select');
        const el = args.el;
        if (sourceIsMediaList && !targetIsMediaList) {
          if (el.parentElement) {
            const index = Array.prototype.indexOf.call(el.parentElement.children, el);
            const videoData = JSON.parse(el.dataset.info);
            el.parentNode.removeChild(el);
            this.addItem(videoData, index);
          }
        }
      });
      service.options('playlist', {
        //mirrorContainer: this.$refs.picker,
        copy: function(el, source) {
          const sourceIsMediaList = source.classList.contains('videos-picker__draggable-container--select');
          return sourceIsMediaList;
        },
        accepts: function (el, target, source, sibling) {
          const sourceIsMediaList = source.classList.contains('videos-picker__draggable-container--select');
          const targetIsMediaList = target.classList.contains('videos-picker__draggable-container--select');
          if (sourceIsMediaList && !targetIsMediaList) {
            return true;
          }
          if (!sourceIsMediaList && !targetIsMediaList) {
            return true;
          }
          return false;
        },
        revertOnSpill: true
      })
    },
    props: {

      channel: {
        type: Object,
        required: true
      },
      value: {
        required: true,
      }
    }
  }
</script>
<style lang="scss">
  .videos-picker {
    padding: 2.5em 0 0;
    &__inner {
      display: flex;
    }
    &__panel {
      min-height: 10em;
      max-height: 50vh;
      overflow: auto;
      position: relative;
      background: var(--box-element-color);
      width: 25em;
      flex: 1;
      margin: 0 .5em;
      background: linear-gradient(90deg, rgba(255, 255, 255, .01), rgba(255, 255, 255, .11));
      padding: 0;
      border-radius: 0;
      &--files {
        margin: 0 .5em 0 0;
      }
      &--selected {
        margin: 0 0 0 .5em;
      }
    }

    &__item {
      transition: all .4s;
      &--folder {
        cursor: pointer;
      }
      &__inner {
        display: flex;
        align-items: center;
        width: calc(100% - 1em);
        padding: .5em;
        border-bottom: 1px solid rgba(255, 255, 255, .1);
        &:hover {
          background: rgba(255, 255, 255, .05);
        }
      }
      &__folder-contents {
        background: rgba(255, 255, 255, 0.05);
        padding: 0 0 0 1em;
      }
      &--video {

      }
      &__title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
      }
      &__length {
        margin: 0 .5em 0 0;
        font-weight: 600;
      }

      &__thumbnail {
        width: 5em;
        height: 3em;
        background-position: center center!important;
        background-size: cover!important;
        margin: 0 .5em 0 0;
      }
      &__icon {
        margin: 0 .5em 0 0;
      }
      &__info {
        margin: .5em .5em 0 0;
        display: inline-flex;
        align-items: center;
      }
      &__text {
        width: 0;
        flex: 1;
        font-size: .875em;
      }
      &__delete {
        margin: 0 0 0 .5em;
        border-bottom: 1px dashed;
        cursor: pointer;
      }
      &__right {
        white-space: nowrap;
        display: flex;
      }


      &__time {
        font-size: .875em;
        margin: .125em 0 0 .5em;
        font-weight: 400;
      }

      &__button {
        opacity: .75;
        cursor: pointer;
        margin: 0 0 0 1em;

        &:hover {
          opacity: .95;
        }
      }

    }
    &__draggable-container {
      &--selected {
        height: 100%;
      }
    }
    &__bottom {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1em 0 0;
    }

    &__total-length {
      font-weight: 300;
    }
  }
</style>
