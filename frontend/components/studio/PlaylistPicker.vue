<template>
  <div class="playlist-picker" ref="picker">
    <div class="playlist-picker__panel playlist-picker__panel--files">
      <c-preloader block  v-if="loading" />
      <div class="playlist-picker__item playlist-picker__item--project" :key="'playlists_'+$index" v-for="(item, $index) in data.playlists" @click="goToProject(item)">
        <div class="playlist-picker__item__icon">
          <i class="material-icons">folder_open</i>
        </div>
        <div class="playlist-picker__item__text">
          {{item.name}}
        </div>
      </div>
      <div  v-if="folders.length > 0"  class="playlist-picker__item playlist-picker__item--folder" @click="goBack()">
        <div class="playlist-picker__item__icon">
          <i class="material-icons">folder_open</i>
        </div>
        <div class="playlist-picker__item__text">
          {{$t('playlistpicker.go_back')}}
        </div>
      </div>
      <div class="playlist-picker__item playlist-picker__item--folder" :key="'folders_'+$index" v-for="(item, $index) in data.folders" @click="goToFolder(item)">
        <div class="playlist-picker__item__icon">
          <i class="material-icons">folder_open</i>
        </div>
        <div class="playlist-picker__item__text">
          {{item.title}}
        </div>
      </div>
      <div class="playlist-picker__draggable-container playlist-picker__draggable-container--videos" v-dragula="data.videos" :drake="drakeName">
        <div  class="playlist-picker__item playlist-picker__item--video" :data-info="JSON.stringify({inPlaylist:false, index: $index, video: item})" :key="'videos_'+$index" v-if="item.is_on_server" v-for="(item, $index) in data.videos">
          <div class="playlist-picker__item__thumbnail" :style="`background:url('${item.thumbnail}') no-repeat center center; background-size: cover;`"></div>
          <div class="playlist-picker__item__text">
            <span class="playlist-picker__item__title">{{item.title}}</span>
            <div class="playlist-picker__item__info">
              <span class="playlist-picker__item__length">{{formatDuration(item.length)}}</span>
             </div>
          </div>
        </div>
      </div>
    </div>
    <div class="playlist-picker__panel playlist-picker__panel--selected">
      <div class="playlist-picker__draggable-container playlist-picker__draggable-container--selected" :class="{'playlist-picker__draggable-container--empty': selectedVideos.length === 0}" v-dragula="selectedVideos" :drake="drakeName">
        <div v-if="item && item.id" class="playlist-picker__item playlist-picker__item--video" :key="'selected_'+$index" v-for="(item, $index) in selectedVideos">
            <div class="playlist-picker__item__thumbnail" :style="`background:url('${item.thumbnail}') no-repeat center center; background-size: cover;`"></div>
            <div class="playlist-picker__item__text">
              <span class="playlist-picker__item__title">{{item.title}}</span>
              <div class="playlist-picker__item__info">
                <span class="playlist-picker__item__length">{{formatDuration(item.length)}}</span>
                <a @click="selectedVideos.splice($index, 1)" class="playlist-picker__item__delete">{{$t('playlistpicker.delete')}}</a>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
  import { formatDuration } from '@/helpers/dates';
  export default {
    beforeDestroy() {
      this.$dragula.$service.drakes[this.drakeName].destroy();
    },
    data() {
      return {
        drakeName: 'drake_' + Math.ceil(Math.random() * 10000),
        selectedVideos: this.value || [],
        currentProjectId: null,
        currentFolderId: null,
        folders: [],
        data: [],
        loading: true,
      }
    },
    watch: {
      selectedVideos(newList) {
        this.$emit('input', newList);
      }
    },
    methods: {
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
      this.$emit('input', this.selectedVideos);
    },
    created() {
      const service = this.$dragula.$service;
      //let name = this.drakeName;
      if (!window.playlistPickerEventInitialised) {
        //window.playlistPickerEventInitialised = true;
        service.eventBus.$on('drop', (args) => {
          if (args.name === this.drakeName) {
            const sourceIsMediaList = args.source.classList.contains('playlist-picker__draggable-container--videos');
            const targetIsMediaList = args.container.classList.contains('playlist-picker__draggable-container--videos');
            const el = args.el;
            if (!el.parentElement) {
              console.log('no parentelement', args);
            }
            if (sourceIsMediaList && !targetIsMediaList) {
              if (el.parentNode) {
                const index = el.parentElement ? Array.prototype.indexOf.call(el.parentNode.children, el) : 0
                const videoData = JSON.parse(el.dataset.info);
                const video = videoData.video;
                el.parentNode.removeChild(el);
                this.selectedVideos.splice(index, 0, video);

              }
            }
            this.selectedVideos = this.selectedVideos.filter(item => (item && item.id));
          }
        });

      }
      service.options(this.drakeName, {
        //mirrorContainer: this.$refs.picker,
        copy: function(el, source) {
          const sourceIsMediaList = source.classList.contains('playlist-picker__draggable-container--videos');
          return sourceIsMediaList;
        },
        accepts: function (el, target, source, sibling) {
          const sourceIsMediaList = source.classList.contains('playlist-picker__draggable-container--videos');
          const targetIsMediaList = target.classList.contains('playlist-picker__draggable-container--videos');
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
  .playlist-picker {
    display: flex;
    &__panel {
      min-height: 10em;
      max-height: 50vh;
      overflow: auto;
      position: relative;
      background: var(--box-element-color);
      padding: .5em;
      border-radius: var(--border-radius);
      width: 25em;
      flex: 1;
      margin: 0 .5em;
      &--files {
        margin: 0 .5em 0 0;
      }
      &--selected {
        margin: 0 0 0 .5em;
      }
    }

    &__item {
      cursor: pointer;
      display: flex;
      align-items: center;
      transition: all .4s;
      &:hover {
        background: rgba(255, 255, 255, .05);
      }
      &--video {
        margin: .5em 0;
      }
      &__title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        max-width: 22.5em;
      }
      &__length {
        margin: .25em 0 0;
        font-weight: 600;
      }

      &__thumbnail {
        width: 5em;
        height: 3em;
        background-size: cover!important;
      }
      &__text {
        font-size: .875em;
        margin: 0 0 0 .5em;
      }
      &__delete {
        margin: 0 0 0 .5em;
        border-bottom: 1px dashed;
        cursor: pointer;
      }
    }
    &__draggable-container {
      &--selected {
        height: 100%;
      }
    }
  }
</style>
