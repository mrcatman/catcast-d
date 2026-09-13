<template>
  <div class="playlist-picker-advanced" ref="picker">
    <VideoMetadataEditor @close="() => playlistItemEditPanel.visible = false" :visible="playlistItemEditPanel.visible" v-model="playlistItemEditPanel.data" @save="savePlaylistItem"/>
    <div class="playlist-picker-advanced__inner">
      <div class="playlist-picker-advanced__panel playlist-picker-advanced__panel--files">
        <c-preloader block  v-if="loading" />
        <div v-if="folders.length > 0"  class="playlist-picker-advanced__item playlist-picker-advanced__item--folder" @click="goBack()">
          <div class="playlist-picker-advanced__item__inner">
            <div class="playlist-picker-advanced__item__icon">
              <i class="material-icons">folder_open</i>
            </div>
            <div class="playlist-picker-advanced__item__text">
              {{$t('playlistpicker.go_back')}}
            </div>
          </div>
        </div>
        <div class="playlist-picker-advanced__draggable-container playlist-picker-advanced__draggable-container--videos"  drake="playlist" v-dragula="getItems">
          <div class="playlist-picker-advanced__item-container" :data-info="JSON.stringify(item.stringable)" :key="$index" v-for="(item, $index) in getItems">

            <div class="playlist-picker-advanced__item playlist-picker-advanced__item--project"  @click="goToProject(item)" v-if="item._type === 'playlists'">
              <div class="playlist-picker-advanced__item__inner">
                <div class="playlist-picker-advanced__item__icon">
                  <i class="material-icons">folder_open</i>
                </div>
                <div class="playlist-picker-advanced__item__text">
                  {{item.name}}
                </div>
              </div>
            </div>

            <div class="playlist-picker-advanced__item playlist-picker-advanced__item--folder" v-if="item && item._type === 'folders'">
              <div class="playlist-picker-advanced__item__inner">
                <div class="playlist-picker-advanced__item__icon" @click="goToFolder(item)">
                  <i class="material-icons">folder_open</i>
                </div>
                <div class="playlist-picker-advanced__item__text">
                  <span class="playlist-picker-advanced__item__title" @click="goToFolder(item)">{{item.title}}</span>
                  <span class="playlist-picker-advanced__item__info">
                    <span class="playlist-picker-advanced__item__buttons">
                      <c-button @click="addItem(item)" flat icon="chevron_right" >{{$t('global.add')}}</c-button>
                    </span>
                  </span>
                </div>
              </div>
            </div>

            <div class="playlist-picker-advanced__item playlist-picker-advanced__item--video" v-else-if="item && item.is_on_server">
              <div class="playlist-picker-advanced__item__inner">
                <div class="playlist-picker-advanced__item__thumbnail" :style="`background:url('${item.thumbnail}') no-repeat center center; background-size: cover;`"></div>
                <div class="playlist-picker-advanced__item__text">
                  <span class="playlist-picker-advanced__item__title">{{item.title}}</span>
                  <span class="playlist-picker-advanced__item__info">
                    <span class="playlist-picker-advanced__item__length">{{getTime(item)}}</span>
                    <span class="playlist-picker-advanced__item__buttons">
                      <c-button @click="addItem(item)" flat icon="chevron_right" >{{$t('global.add')}}</c-button>
                    </span>
                  </span>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="playlist-picker-advanced__panel playlist-picker-advanced__panel--selected" ref="selected_panel">
        <div class="playlist-picker-advanced__draggable-container playlist-picker-advanced__draggable-container--selected" :class="{'playlist-picker-advanced__draggable-container--empty': selectedVideos.length === 0}" v-dragula="selectedVideos" drake="playlist">
          <div v-if="item !== null && item !== undefined" class="playlist-picker-advanced__item playlist-picker-advanced__item--video" :key="item.id+'_selected_'+$index" v-for="(item, $index) in selectedVideos">
            <div class="playlist-picker-advanced__item__inner">
              <div class="playlist-picker-advanced__item__text">
                <span class="playlist-picker-advanced__item__title">{{item.title}}</span>
                <div class="playlist-picker-advanced__item__info" v-if="item.is_folder">
                  <c-tag >{{$t('scheduler.playlists.is_folder')}}</c-tag>
                </div>
              </div>
              <div class="playlist-picker-advanced__item__right">
                <div class="playlist-picker-advanced__item__time">
                  {{getTime(item, $index)}}
                </div>
                <a @click="openPlaylistItemEditPanel(item)" class="playlist-picker-advanced__item__button">
                  <i class="fa fa-edit"></i>
                </a>
                <a @click="deleteItemFromPlaylist(item)" class="playlist-picker-advanced__item__button">
                  <i class="fa fa-times"></i>
                </a>
              </div>
            </div>
            <div :ref="`folder${item.video_folder_id}`" class="playlist-picker-advanced__item__folder-contents" v-if="item && item.is_folder" v-dragula="item.items" drake="playlist">
              <div v-if="folderItem" :key="folderItem.video_id ? folderItem.video_id+'_'+$index2 : folderItem.id+'_'+$index2" v-for="(folderItem, $index2) in item.items" class="playlist-picker-advanced__item">
                <div class="playlist-picker-advanced__item__inner">
                  <div class="playlist-picker-advanced__item__text">
                    <span class="playlist-picker-advanced__item__title">{{folderItem.title}}</span>
                    <div class="playlist-picker-advanced__item__info">

                    </div>
                  </div>
                  <div class="playlist-picker-advanced__item__right">
                    <div class="playlist-picker-advanced__item__time">
                      {{formatDuration(folderItem.length, $index, $index2)}}
                    </div>
                    <a @click="openPlaylistItemEditPanel(folderItem)" class="playlist-picker-advanced__item__button">
                      <i class="fa fa-edit"></i>
                    </a>
                    <a @click="deleteItemFromPlaylist(folderItem, $index)" class="playlist-picker-advanced__item__button">
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
    <div class="playlist-picker-advanced__bottom">
      <c-button @click="save()">{{$t('global.save')}}</c-button>
      <div class="playlist-picker-advanced__total-length">{{$t('scheduler.playlists.total_length')}} <strong>{{totalLength}}</strong></div>
    </div>
  </div>
</template>
<script>
  import VideoMetadataEditor from '@/components/scheduler/VideoMetadataEditor';
  import { formatDuration } from '@/helpers/dates';
  export default {
    components: {
      VideoMetadataEditor
    },
    computed: {
      isClassicPlaylist() {
        return this.playlist && this.playlist.data && this.playlist.data.type === "default";
      },
      totalLength() {
        let length = 0;
        this.selectedVideos.forEach(item => {
          if (item) {
            if (item._length) {
              length += parseInt(item._length);
            } else {
              if (item.is_folder) {
                if (item.items) {
                  item.items.forEach(video => {
                    if (video && video.length) {
                      length += parseInt(video.length);
                    }
                  })
                }
              } else {
                if (item && item.length) {
                  length += parseInt(item.length);
                }
              }
            }
          }
        });
        return formatDuration(length);
      },
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
        selectedVideos: this.value,
        currentProjectId: null,
        currentFolderId: null,
        folders: [],
        data: {
          videos: [],
          folders: [],
          playlists: [],
        },
        loading: true,
        playlistItemEditPanel: {
          editIndex: -1,
          playlistEditIndex: -1,
          visible: false,
          errors: {},
          data: {
            title: '',
            description: '',
            tags: []
          }
        },
      }
    },
    beforeDestroy() {
      if (this.$dragula.$service.drakes.playlist) {
        delete this.$dragula.$service.drakes.playlist;
      }
    },
    watch: {
      selectedVideos(newList) {
        this.$emit('input', newList);
      }
    },
    methods: {
      addItem(video, index = null) {
         if (video._type === "folders") {
          video.is_folder = true;
        }

        if (video.is_folder) {
          let playlist = this.playlist;
          let isDefault = playlist && playlist.data && playlist.data.type === "default";
          this.setFolderData(video, isDefault);
        } else {
            if (!video.video_id && video.id) {
                video.video_id = video.id;
            }
        }
        console.log('add video', video);
        this.selectedVideos = this.selectedVideos.filter(item => (item));

        if (index) {
          this.selectedVideos.splice(index, 0, video);
        } else {
          this.selectedVideos.push(video);
          this.$nextTick(() => {
            let panel = this.$refs.selected_panel;
            panel.scrollTop = panel.scrollHeight;
          })
        }
      },
      save() {
        this.$emit('save', this.selectedVideos);
      },
      deleteItemFromPlaylist(item, folderIndex) {
        if (folderIndex) {
          this.selectedVideos[folderIndex].splice(this.selectedVideos[folderIndex].indexOf(item), 1);
        }
        this.selectedVideos.splice(this.selectedVideos.indexOf(item), 1);
      },

      getTime(item, index) {
        if (!item) {
          return "";
        }
        if (!this.showItemDates) {
          if (item._length) {
            return formatDuration(item._length);
          }
          if (item.is_folder) {
            let length = 0;
            if (item.items) {
              item.items.forEach(video => {
                if (video && video.length) {
                  length += parseInt(video.length);
                }
              })
            }
            if (!length) {
              return "";
            }
            return formatDuration(length);
          }
          if (!item.length) {
            return "";
          }
          return formatDuration(item.length);
        }
        return "";
      },
      async setFolderData(folder, isDefault) {
         let data = {};

        if (!folder.is_project) {
          data.folder_id = folder.video_folder_id ?  folder.video_folder_id :  folder.id;
        } else {
          data.project_id = folder.video_folder_id ?  -1 * folder.video_folder_id : folder.id;
        }
        let res = (await this.$axios.post(`videos/filemanager/${this.channel.id}`, data)).data;
        if (res.status) {
          let folderData = this.selectedVideos.filter(item => item.video_folder_id === folder.video_folder_id)[0];
          if (folderData) {
            if (isDefault) {
              this.$set(folderData, 'items', res.data.videos.filter(video => video.is_on_server).map(video => {
                return {
                  video_id: video.id,
                  title: video.title,
                  length: video.length
                };
              }));
              let ref = this.$refs['folder' + folder.video_folder_id];
              if (ref && ref[0]) {
                ref = ref[0];
              }
              this.$dragula.$service.drakes.playlist.models.push({
                container: ref,
                model: folderData.items,
              });
            } else {
              let totalLength = 0;
              res.data.videos.filter(video => video.is_on_server).forEach(video => {
                totalLength+= video.length;
              });
              this.$set(folder, '_length', totalLength);
            }
          }
        }
      },

      savePlaylistItem(data) {
        this.$set(this.selectedVideos, this.playlistItemEditPanel.editIndex,  data);
        this.playlistItemEditPanel.visible = false;
      },
      openPlaylistItemEditPanel(item) {
        this.playlistItemEditPanel.editIndex = this.selectedVideos.indexOf(item);
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
      goToProject(project) {
        this.currentProjectId = project.id;
        this.folders.push({
          id: project.id,
          title: project.name,
          is_project: true,
        });
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
        } else {
          if (this.currentProjectId > 0) {
            data.project_id = this.currentProjectId;
          }
        }
        this.loading = true;
        this.$axios.post(`videos/filemanager/${this.channel.id}`, data).then (res => {
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
        const sourceIsMediaList = args.source.classList.contains('playlist-picker-advanced__draggable-container--videos');
        const targetIsMediaList = args.container.classList.contains('playlist-picker-advanced__draggable-container--videos');
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
          const sourceIsMediaList = source.classList.contains('playlist-picker-advanced__draggable-container--videos');
          return sourceIsMediaList;
        },
        accepts: function (el, target, source, sibling) {
          const sourceIsMediaList = source.classList.contains('playlist-picker-advanced__draggable-container--videos');
          const targetIsMediaList = target.classList.contains('playlist-picker-advanced__draggable-container--videos');
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
      playlist: {
        type: Object,
        required: true
      },
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
  .playlist-picker-advanced {
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
