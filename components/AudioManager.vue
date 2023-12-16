<template>
  <div class="audio-manager">

    <input @change="onFileInputChange" type="file" style="display:none" multiple ref="fileInput"/>
    <TrackModal :isInPublicFolder="currentFolder.is_public" :data="trackEditPanel.data" @editTrack="editTrack" v-model="trackEditPanel.visible" v-if="trackEditPanel.visible" />
    <FolderModal @newfolder="newFolder" @editfolder="editFolder" :folderId="currentFolderId" :channel="channel" v-model="addFolderPanel"  />


    <c-modal v-model="moveToFolderPanel.visible">
      <div slot="main">
        <div class="modal__text">{{$t('dashboard.tracks.move_to_folder_text')}}</div>
        <div class="modal__input-container">
          <c-select :showEmptyVariant="false" :options="foldersToMoveTo" v-model="moveToFolderPanel.id" />
        </div>
      </div>
      <div class="modal__buttons" slot="buttons">
        <div class="buttons-row">
          <c-button @click="moveToFolderPanel.visible = false; moveToFolder()">{{$t('global.ok')}}</c-button>
          <c-button color="red" @click="moveToFolderPanel.visible = false;">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>

    <c-modal v-if="folderToDelete.data" :showCloseButton="false" :header="$t('dashboard.tracks.delete_folder._title')">
      <div slot="main">
        <div class="modal__text">{{$t('dashboard.tracks.delete_folder._text')}}</div>
        <div class="modal__input-container">
          <c-select :placeholder="$t('dashboard.tracks.delete_folder.action')" :options="deleteFolderActions" v-model="folderToDelete.action" />
        </div>
        <div class="modal__input-container">
          <c-select v-if="folderToDelete.action === 1" :placeholder="$t('dashboard.tracks.delete_folder.move_to')" :options="deleteFolderMoveTo" v-model="folderToDelete.moveTo" />
        </div>
      </div>
      <div class="modal__buttons" slot="buttons">
        <div class="buttons-row">
          <c-button :loading="folderToDelete.loading" flat @click="deleteFolder()">{{$t('global.ok')}}</c-button>
          <c-button color="red" @click="folderToDelete.data = null;">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>




    <c-preloader block  v-show="loading" />
    <div class="audio-manager__header">
      <div class="audio-manager__top-buttons" v-if="showTopPanel">
        <div class="buttons-row" >
          <c-button @click="openFilePicker()" icon="queue_music" color="green">{{$t('dashboard.tracks.add_files_button')}}</c-button>
          <c-button @click="addFolderPanel.visible = true" icon="folder_special" >{{$t('dashboard.tracks.add_folder_button')}}</c-button>
        </div>
      </div>
      <div class="audio-manager__header__search">
        <c-input :title="$t('dashboard.tracks.search')" @change="goSearch()" :loading="search.loading" v-model="search.input" icon="search" />
      </div>
    </div>
    <div v-dragula="tracksList" :drake="drakeName" class="audio-manager__tracks" @drop="onDrop" @dragover.prevent="onDragStatusChange(true)" @dragleave="onDragStatusChange(false)">
      <div v-show="isDragging" class="audio-manager__dragover-panel">
        <div class="audio-manager__dragover-panel__title">{{$t('dashboard.tracks.dragover_title')}}</div>
      </div>
      <div class="audio-track" :data-info="JSON.stringify({inPlaylist:false,index:$index,track:track})" :class="{'audio-track--selected': track._selected,'audio-track--folder':track.is_folder}" :key="track.id" v-for="(track,$index) in tracksList">
        <div class="audio-track__col audio-track__col--icons">
          <div v-show="!track.is_folder" class="audio-track__checkbox">
            <c-checkbox v-model="track._selected" @click="selectTrack(track,true)" />
          </div>
          <div v-if="track._is_back_folder" class="audio-track__folder-icon-container">
            <i class="material-icons">keyboard_backspace</i>
          </div>
          <div v-else-if="track.is_folder" class="audio-track__folder-icon-container">
            <i class="material-icons">folder_open</i>
          </div>
        </div>
        <div class="audio-track__col audio-track__col--main" @click="selectTrack(track,true)">
          <div class="audio-track__main__inner">
            <span :style="{width:(track._uploadProgress * 100)+'%'}" v-if="track.upload_status === 'STATUS_UPLOADING'" class="audio-track__upload-progress"></span>
            <div class="audio-track__inner">
              <span class="audio-track__filename" v-if="track._is_back_folder">{{$t('dashboard.tracks._table.back_folder')}}</span>
              <span class="audio-track__filename" v-else-if="track.is_folder">{{track.folder_title}}</span>
              <div class="audio-track__info-block" v-else-if="track.title">
                <span class="audio-track__title">{{track.title}}</span>
                <span class="audio-track__artist">{{track.author}}</span>
              </div>
              <span class="audio-track__filename" v-else-if="track.filename">{{track.filename}}</span>
            </div>
          </div>
        </div>
        <div v-if="showEditButtons" class="audio-track__col audio-track__col--actions">
          <div class="audio-track__buttons" v-if="!track._is_back_folder">
            <div class="buttons-row">
              <div class="audio-track__handle" v-if="dragHandles && track.upload_status === 'STATUS_READY'">
                <i class="fa fa-arrows-alt"></i>
              </div>
              <c-button @click="playTrack(track)" v-if="!track.is_folder && track.upload_status === 'STATUS_READY'" rounded icon="play_arrow" />
              <c-button flat rounded icon="edit" icon-only @click="trackEditPanel.data = track; trackEditPanel.visible = true;"></c-button>
              <c-button flat rounded icon="delete" icon-only @click="deleteTrack(track)"></c-button>
            </div>
          </div>
        </div>
      </div>

    </div>
    <div class="audio-manager__bottom-panel">
      <div class="audio-manager__buttons-rows-container">
        <div class="buttons-row">
          <c-button v-if="selectedTracks.length > 0 && showPlaylistButtons" @click="moveToPlaylist()">{{$t('dashboard.tracks.move_to_playlist')}}</c-button>

          <c-button @click="moveToFolderPanel.visible = true" v-if="selectedTracks.length > 0 && showControlButtons" icon="folder">{{$t('dashboard.tracks.move_to_folder')}}</c-button>
          <c-button v-if="selectedTracks.length > 0 && showControlButtons" @click="batchAction('delete_finally')" icon="delete" color="red" >{{$t('global.delete')}}</c-button>
          <c-button @click="reloadTracks()" flat icon-only icon="refresh"></c-button>
        </div>
        <div class="audio-manager__folder-size" v-if="diskSpace">
          <div class="audio-manager__folder-size__occupied">{{bytesToFileSize(diskSpace.data.space_occupied)}}</div>
          <div class="audio-manager__folder-size__bar">
            <div :style="{width:(diskSpace.data.space_occupied/diskSpace.data.space_total*100)+'%'}" class="audio-manager__folder-size__bar__occupied"></div>
          </div>
          <div class="audio-manager__folder-size__total">{{bytesToFileSize(diskSpace.data.space_total)}}</div>
        </div>
      </div>
      <div class="audio-manager__pager-container" >
        <c-pager :data="tracks" v-model="currentPage" />
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .audio-manager {
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
    &__tracks {
      background: linear-gradient(90deg, rgba(255, 255, 255, 0.01176), rgba(255, 255, 255, 0.1098));
      flex: 1;
      overflow: auto;
      overflow-x: hidden;
    }
    &__pager-container {
      margin: 0 0 0 1em;
      font-size: .75em;
    }
    &__bottom-panel {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1em;
      background: var(--box-footer-color);
    }
    &__header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 1em;
      background: var(--box-footer-color);
      &__search {
        flex: 1;
        margin: 0 0 0 1em;
      }

      &__bottom-panel {
        height: 2em;
        background: var(--box-element-color);
      }
    }
    &__dragover-panel {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: stretch;
      justify-content: stretch;
      background: rgba(0, 0, 0, 0.55);
      z-index: 1000;
      text-align: center;
      &__title {
        font-size: 2em;
        margin: 1em;
        padding: 1em;
        width: 100%;
        border: .25em dashed #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
      }
    }
    &__buttons-lists-container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      .buttons-list {
        font-size: .875em;
      }
    }
    &__folder-size {
      display: flex;
      align-items: center;
      flex: 1;
      font-size: .875em;
      max-width: 20em;
      &__bar {
        flex: 1;
        background: rgba(255, 255, 255, 0.1);
        height: 1em;
        border-radius: 1em;
        margin: 0 1em;
        overflow: hidden;
        &__occupied {
          height: 100%;
          background: var(--active-color);
        }
      }
    }
  }
</style>
<script>
  import Selection from '@/helpers/selection/selection.js';
  import TrackModal from '@/components/dashboard/tracks/trackModal';
  import FolderModal from '@/components/dashboard/tracks/folderModal';
  export default {
    beforeDestroy() {
      if (this.$dragula.$service.drakes[this.drakeName]) {
        delete this.$dragula.$service.drakes[this.drakeName];
      }
      if (this.selection) {
        this.selection.destroy()
      }
    },
    created() {

    },
    components: {
      TrackModal, FolderModal
    },
    computed: {
      foldersToMoveTo() {
        let folders = this.folders.filter(folder => folder.id !== this.currentFolderId).map(folder => {
          return {
            name: folder.folder_title,
            value: folder.id
          }
        });
        if (this.currentFolderId !== -1) {
          folders.push(
            {
              name: this.$t('dashboard.tracks.main_folder'),
              value: -1,
            }
          );
        }
        return folders;
      },
      deleteFolderMoveTo() {
        let moveVariants = this.folders.filter(folder => folder.id !== this.folderToDelete.data.id).map(folder=>{
          return {
            value:folder.id,
            name:folder.folder_title,
          }
        });
        moveVariants.unshift(
          {
            value: -1,
            name: this.$t('dashboard.tracks.main_folder')
          }
        );
        return moveVariants;
      },
      deleteFolderActions() {
        let actions = [
          {value:0,name:this.$t('dashboard.tracks.delete_folder.with_tracks')},
        ];
        if (this.deleteFolderMoveTo.length > 0) {
          actions.push(
            {value:1,name:this.$t('dashboard.tracks.delete_folder.move_tracks')},
          );
        }
        return actions;
      },
      tracksList() {
        let allTracks = {};
        if (this.search.input && this.search.input !== "" && this.search.results && this.search.results.data) {
          allTracks = this.search.results.data;
        } else {
          if (!this.tracks.data) {
            return [];
          }
          let tracks = this.tracks.data;
          let loading = this.uploadingFilesList.filter(track=>track.folder_id === this.currentFolderId);
          allTracks = tracks.concat(loading);
        }
        let selectedIDs = this.selectedTracks.map(track => track.id);
        allTracks = allTracks.map(track=>{
          track._selected = selectedIDs.indexOf(track.id) !== -1;
          return track;
        }).sort((a, b)=>{
          return (a.is_folder === b.is_folder)? 0 : a.is_folder ? -1 : 1;
        });
        if (this.currentFolderId !== -1 && !this.loading) {
          allTracks.unshift({
            _selected: false,
            is_folder: true,
            _is_back_folder: true,
            id: -1,
          })
        }
        return allTracks;
      },
    },
    async mounted() {
      this.getDiskSpace();
      this.getTracks();
    },
    methods: {
      async getDiskSpace() {
        this.diskSpace = (await this.$axios.post(`/channels/${this.channel.id}/getdiskspace`)).data;
      },
      deleteTrack(track) {
        if (track.is_folder) {
          this.folderToDelete.data = track;
        } else {
          if (! track.id) {
            this.tracks.data.splice(this.tracks.data.indexOf(track), 1);
          } else {
            this.$axios.delete('/tracks/' + track.id).then(res => {
              this.$store.commit('NEW_ALERT', res.data);
              if (res.data.status) {
                this.diskSpace.data.space_occupied = res.data.data.space_occupied;
                this.tracks.data.splice(this.tracks.data.indexOf(track), 1);
              }
            })
          }
        }
      },
      playTrack(track) {
        let data = {
          url: track.client_path,
          author: ""
        };
        if (track.title) {
          data.title = track.title;
          data.author = track.author || "";
        } else {
          data.title = track.filename;
        }
        this.$store.commit("SET_PLAYER_TRACK",data);
      },
      editTrack(track) {

      },
      batchAction(actionType,newId = null, oldId = null, isPlaylistsAction = false) {
        let selectedTracks = isPlaylistsAction ? this.selectedPlaylistTracks : this.selectedTracks;
        let ids = selectedTracks.map(track => track.id);
        let data = {
          ids,
          action_type: actionType
        };
        if (newId) {
          data.new_id = newId;
        }
        if (oldId) {
          data.old_id = oldId;
        }
        this.$axios.post('/tracks/batch',data).then(res=>{
          if (!res.status) {
            this.$store.commit('NEW_ALERT',res.data);
          } else {
            if (actionType === "delete_finally") {
              this.diskSpace.data.space_occupied = res.data.data.space_occupied;
            }
            //this.$store.commit('NEW_ALERT',res.data);
            //if (["copy_to_playlist","copy_to_folder"].indexOf(actionType) !== -1) {

            //} else {
            if (["copy_to_playlist","copy_to_folder"].indexOf(actionType) !== -1) {
              ids = [];
            }
            if (["move_to_playlist"].indexOf(actionType) !== -1 || (actionType === "copy_to_playlist" && newId !== this.currentPlaylistId)) {
              this.reloadPlaylistTracks();
            }
            if (actionType === "move_to_folder") {
              this.removeTracksCache(newId, 1);
            }
            if (res.data.ids) {
              ids = res.data.ids;
            }
            this.selectedTracks = [];
            this.tracks.data = this.tracks.data.filter(track => ids.indexOf(track.id) === -1);
            this.setTracksCache(this.tracks, this.currentPage, this.currentFolderId);


            // }
          }
        })
      },
      moveToFolder() {
        this.batchAction('move_to_folder', this.moveToFolderPanel.id, this.currentFolderId);
      },
      onDragStatusChange(status) {
        if (!this.isResizing) {
          this.isDragging = status;
        }
      },

      onDrop(e) {
        this.isDragging = false;
        if (e.dataTransfer && e.dataTransfer.files) {
          Array.from(e.dataTransfer.files).forEach(file => this.addToUpload(file));
        }
        e.preventDefault();
      },
      bytesToFileSize(bytes) {
        let gb = Math.round(bytes / (1024*1024*1024) * 1000) / 1000;
        gb = gb  + ' GB';
        return gb;
      },
      onFileInputChange(e) {
        if (e.target && e.target.files) {
          Array.from(e.target.files).forEach(file => this.addToUpload(file));
        }
      },
      addToUpload(file) {
        let _id = file.name+'_'+Math.floor(Math.random()*1000);
        let track = {
          _id,
          filename:file.name,
          playlists:[],
          folder_id:this.currentFolderId,
          upload_status:'STATUS_WAITING_FOR_UPLOAD',
          _uploadProgress:0,
          _file:file
        };
        this.uploadingFilesList.push(track);
        //if (!this.isUploading) {
        this.startUpload();
        //}
      },
      uploadFile(track) {
        let file = track._file;
        let fileName = file.name;
        //let hasTrackWithSuchName = this.tracks.list.map(track=>track.filename).indexOf(fileName) !== -1;
        let hasTrackWithSuchName= false;
        //console.log(hasTrackWithSuchName);
        //this.isUploading = true;
        if (hasTrackWithSuchName) {
          this.filesWithNameConflict.push({
            title:file.name,
            status:-1,
            file,
            existingItem:this.tracks.list.filter(track=>track.filename == fileName)[0],
          });
        } else {
          let fd=new FormData();
          fd.append('track',file);
          fd.append('folder_id',track.folder_id);
          fd.append('playlist_id',this.currentPlaylistId);
          fd.append('channel_id',this.channel.id);

          track.upload_status = "STATUS_UPLOADING";
          this.$axios.post('/tracks',fd,{onUploadProgress: progressEvent => {
            track._uploadProgress = ((progressEvent.loaded) / progressEvent.total);
          }}).then(res=>{
            this.uploadingFilesList.splice(this.uploadingFilesList.indexOf(track),1);
            this.startUpload();
            if (res.data.status) {
              this.diskSpace.data.space_occupied = res.data.data.space_occupied;
              this.tracks.data.push(res.data.track);
            } else {
              this.$store.commit('NEW_ALERT',res.data);
            }
          })
        }
      },
      startUpload() {
        let uploadCount = 0;
        const maxUploads = 5;
        uploadCount = this.uploadingFilesList.filter(file=>file.upload_status === "STATUS_UPLOADING").length;
        if (uploadCount < maxUploads) {
          this.uploadingFilesList.forEach(file => {
            if (file.upload_status === "STATUS_WAITING_FOR_UPLOAD" && uploadCount < maxUploads) {
              this.uploadFile(file);
              uploadCount++;
            }
          })
        }
      },
      openFilePicker() {
        this.$refs.fileInput.click();
      },
      editFolder(editedFolder) {
        folder.is_folder = true;
        this.tracks.data.forEach((track,i)=>{
          if (track.id === editedFolder.id) {
            this.$set(this.tracks.data,i,editedFolder);
          }
        })
      },
      newFolder(folder) {
        folder.is_folder = true;
        this.tracks.data.push(folder);
      },
      deleteFolder() {
        let data = this.folderToDelete.action === 0 ? {delete_tracks:true} : {move_tracks_to:this.folderToDelete.moveTo};
        this.$axios.delete('/folders/'+this.folderToDelete.data.id,{data}).then(res=>{
          this.$store.commit('NEW_ALERT',res.data);
          if (res.data.status) {
            this.diskSpace.data.space_occupied = res.data.data.space_occupied;
            if (this.currentFolderId === this.folderToDelete.data.id) {
              this.currentFolderId = this.folderToDelete.data.parent_id;
            }
            this.folders = this.folders.filter(folder => folder.id !== this.folderToDelete.data.id)
            this.tracks.data = this.tracks.data.filter(track => (!track.is_folder || (track.is_folder && track.id !== this.folderToDelete.data.id)));
            this.folderToDelete.data = null;
          }
        });
      },
      removeTracksCache(folderId, page) {
        if (!this.tracksCache[folderId]) {
          return true;
        }
        if (!this.tracksCache[folderId][page]) {
          return true;
        }
        this.tracksCache[folderId][page] = null;
      },
      setPlaylistTracksCache(tracks,playlistId) {
        this.tracksCache[playlistId] = tracks;
      },
      setFoldersCache(folders, parentFolderId) {
        this.foldersCache[parentFolderId] = folders;
      },
      setTracksCache(tracks, page, folderId) {
        if (! this.tracksCache[folderId]) {
          this.tracksCache[folderId] = {};
        }
        this.tracksCache[folderId][page] = tracks;
      },
      goSearch() {
        this.reloadTracks();
      },
      reloadTracks() {
        this.removeTracksCache(this.currentFolderId, this.currentPage);
        this.getTracks();
      },
      moveToPlaylist() {
        this.$emit('moveToPlaylist', this.selectedTracks);
        this.selectedTracks = [];
      },
      enableSelection() {
        let vm = this;
        const options = {
          selectables: ['.audio-track'],
          class: 'selection-area',
          startThreshold: 10,
          disableTouch: false,
          mode: 'touch',
          singleClick: true,
          startareas: ['.audio-manager__tracks'],
          boundaries: ['.audio-manager__tracks'],
          selectionAreaContainer: 'body',
          scrollSpeedDivider: 10,
          validateStart(evt) {
            return evt.path[0].classList.contains('audio-track__col--main');
          },
          onSelect(evt) {
            const selected = evt.target.classList.contains('audio-track--selected') || evt.target.classList.contains('audio-track--selected-with-area');
            if (!evt.originalEvent.ctrlKey && !evt.originalEvent.metaKey) {
              evt.selectedElements.forEach(s => s.classList.remove('audio-track--selected-with-area'));
              this.clearSelection();
            }

            if (!selected) {
              evt.target.classList.add('audio-track--selected-with-area');
              this.keepSelection();
            } else {
              evt.target.classList.remove('audio-track--selected');
              this.removeFromSelection(evt.target);
            }
          },

          onStart({selectedElements, originalEvent}) {
            if (!originalEvent.ctrlKey && !originalEvent.metaKey) {
              selectedElements.forEach(s => s.classList.remove('audio-track--selected-with-area'));
              this.clearSelection();
            }
          },

          onMove({selectedElements, changedElements}) {
            selectedElements.forEach(s => s.classList.add('audio-track--selected-with-area'));
            changedElements.removed.forEach(s => s.classList.remove('audio-track--selected-with-area'));
          },

          onStop(e) {
            let elements = e.selectedElements;
            let tracks = elements.filter(element => element.dataset && element.dataset.info).map(element => JSON.parse(element.dataset.info)).map(info => info.track).filter(track => !track.is_folder);
            let ids = tracks.map(track => track.id);
            vm.selectedTracks = tracks;

            let allTracks = vm.tracks.data;
            if (vm.search.input && vm.search.input !== "" && vm.search.results) {
              allTracks = vm.search.results.data;
            }
            allTracks.forEach(track => {
              vm.$set(track, '_selected', (ids.indexOf(track.id) !== -1));
            });
            vm.$forceUpdate();

            this.keepSelection();
          }
        };
        this.selection = Selection.create(options);
      },
      selectTrack(track) {
        if (!track.is_folder) {
          let ids = this.selectedTracks.map(track => track.id);
          if (ids.indexOf(track.id) !== -1) {
            this.selectedTracks.splice(ids.indexOf(track.id), 1);
          } else {
            this.selectedTracks.push(track);
          }
        } else {
          if (track._is_back_folder) {
            if (this.parentsCache[this.currentFolderId]) {
              this.currentFolderId = this.parentsCache[this.currentFolderId];
            } else {
              this.currentFolderId = this.currentFolder.parent_id;
            }
            if (this.currentFolderId === -1) {
              this.currentFolder = {
                is_public: false
              }
            }
          } else {
            this.parentsCache[track.id] = this.currentFolderId;
            this.currentFolder = track;
            this.currentFolderId = track.id;
          }
        }
      },
      getTracks() {
        if (this.tracksCache[this.currentFolderId] && this.tracksCache[this.currentFolderId][this.currentPage]) {
          this.tracks = this.tracksCache[this.currentFolderId][this.currentPage];
          if (this.foldersCache[this.currentFolderId]) {
            this.folders = this.foldersCache[this.currentFolderId];
          }
        } else {
          this.loading = true;
          let data = {};
          if (this.currentFolderId > -1) {
            data.folder_id = this.currentFolderId;
          }
          if (this.search && this.search.input !== "") {
            data.search = this.search.input;
          }
          data.page = this.currentPage;
          this.$axios.post(`/tracks/getbychannel/${this.channel.id}`, data).then(res => {
            this.loading = false;
            if (res.data.status) {
              if (this.search.input && this.search.input !== "") {
                this.search.results = res.data.data.list;
              } else {
                this.folders = res.data.data.folders;
                this.tracks = res.data.data.list;
                this.$nextTick(() => {
                  if (!this.disableSelection) {
                    this.enableSelection();
                  }
                })
              }
            } else {
              this.$store.commit('NEW_ALERT', res.data);
            }
          });
        }
      },

    },
    watch: {
      selectedTracks(tracksList) {
        this.selectAllHandler = (tracksList.length === this.tracks.data.filter(track=>!track.is_folder).length);
      },
      currentPage(newPage, oldPage) {
        this.setTracksCache(this.tracks, oldPage, this.currentFolderId);
        this.getTracks();
      },
      currentFolderId(newId,oldId) {
        this.search.input = "";
        this.setTracksCache(this.tracks, this.currentPage, oldId);
        this.getTracks();
      }
    },
    data() {
      return {
        diskSpace: null,
        selection: null,
        currentFolder: {
          is_public: false
        },
        moveToFolderPanel: {
          id: -1,
          visible: false,
        },
        folderToDelete: {
          data: null,
          variant: 0,
          moveTo: -1,
        },
        currentFolderId: -1,
        uploadingFilesList: [],
        loadingFiles: false,
        isDragging: false,
        selectedTracks: [],
        foldersCache: {},
        parentsCache: {},
        currentPage: 1,
        selectAllHandler: false,
        search: {
          input: '',
          results: {}
        },
        loading: true,
        tracksCache: {},
        tracks: {},
        folders: [],
        trackEditPanel: {
          visible: false,
          data: null
        },
        addFolderPanel: {
          visible: false,
          name: '',
        },
      }
    },
    props: {
      disableSelection: {
        type: Boolean,
        required: false
      },
      dragHandles: {
        type: Boolean,
        required: false,
      },
      drakeName: {
        type: String,
        required: true
      },
      showEditButtons: {
        type: Boolean,
        required: false
      },
      showTopPanel: {
        type: Boolean,
        required: false
      },
      showControlButtons: {
        type: Boolean,
        required: false
      },
      showPlaylistButtons: {
        type: Boolean,
        required: false,
      },
      channel: {
        type: Object,
        required: true
      }
    }
  }
</script>
