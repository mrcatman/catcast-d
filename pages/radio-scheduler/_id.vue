<template>
  <div class="radio-scheduler__outer">
    <!--<PlaylistPanel class="modal-panel--right" :class="{'modal-panel--visible':panelsVisible.playlists}" @edit="editPlaylist" @newplaylist="addPlaylist" @close="panelsVisible.playlists = false" :channel="channel" :data="currentEditing.playlist" />-->


    <announcePanel @close="() => announcePanel.visible = false" :isEditing="announcePanel.isEditing" :broadcastingUsers="broadcastingUsers" :playlistsList="playlistsList" :isChannelAdmin="isChannelAdmin" :canAddAnnounces="canAddAnnounces" :visible="announcePanel.visible" @save="onAnnounceSaved" @deleted="onAnnounceDeleted" :channel="channel" v-model="announcePanel.data"/>
    <playlistEditor @newplaylist="addPlaylist" @editplaylist="editPlaylist" v-model="playlistPanel.visible" :channel="channel" :data="playlistPanel.data"/>

    <c-modal v-if="playlistToDelete.data" :showCloseButton="false" :header="$t('dashboard.tracks.playlists.delete_playlist._title')">
      <div slot="main">
        <div class="modal__text">{{$t('dashboard.tracks.playlists.delete_playlist._text')}}</div>
        <div class="modal__input-container">
          <c-select :placeholder="$t('dashboard.tracks.playlists.delete_playlist.action')" :options="deletePlaylistActions" v-model="playlistToDelete.action" />
        </div>
        <div class="modal__input-container">
          <c-select v-if="playlistToDelete.action === 1" :placeholder="$t('dashboard.tracks.playlists.delete_playlist.move_to')" :options="deletePlaylistMoveTo" v-model="playlistToDelete.move_to" />
        </div>
      </div>
      <div class="modal__buttons" slot="buttons">
        <div class="buttons-row">
          <c-button :loading="playlistToDelete.loading" @click="deletePlaylist()">{{$t('global.ok')}}</c-button>
          <c-button color="red" @click="playlistToDelete.data = null;">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>

    <c-modal v-model="playlistDeletePanel.visible">
      <div slot="main">
        <div class="modal__text">{{$t('dashboard.tracks.delete_playlist._text')}}</div>
      </div>
      <div class="modal__buttons" slot="buttons">
        <div class="buttons-row">
          <c-button :loading="playlistDeletePanel.loading" flat @click="continueDeletingCurrentPlaylist()">{{$t('global.ok')}}</c-button>
          <c-button color="red" @click="playlistDeletePanel.visible = false;">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>

    <div class="radio-scheduler__main-content" ref="main">
      <div class="radio-scheduler" >
       <div class="radio-scheduler__main">
          <ResizableRow @resizestart="isResizing = true" @resizeend="isResizing = false" name="tracks_page_row">
            <ResizableRowChild :defaultWidth="0.45">
              <div class="radio-scheduler__audio-manager-container">
                <AudioManager @moveToPlaylist="onMoveToPlaylist" :showPlaylistButtons="true" :showControlButtons="true" :showTopPanel="true" :showEditButtons="true" :drakeName="'tracks'" :channel="channel"/>
              </div>
            </ResizableRowChild>
           <ResizableRowBar/>
           <ResizableRowChild :defaultWidth="0.55">
             <div class="radio-scheduler__playlists-container">
               <div class="radio-scheduler__tabs">
                 <c-tabs :data="tabs" v-model="currentTab"/>
               </div>
               <div class="radio-scheduler__playlists" v-if="currentTab === 'playlists'">
                 <div class="radio-scheduler__playlists__header">
                   <div class="radio-scheduler__playlists__header__left">
                     <span class="radio-scheduler__playlists__header__text" style="display:none">{{$t('dashboard.tracks.playlists._title')}}</span>
                     <div class="buttons-row">
                       <c-button @click="showPlaylistAddPanel()" icon="playlist_add">{{$t('global.add')}}</c-button>
                     </div>
                   </div>
                   <div class="radio-scheduler__playlists__header__right">
                     <div v-show="playlists.length > 0" class="radio-scheduler__playlists__header__select">
                      <c-select  placeholder="" :showEmptyVariant="false" :options="playlistsOptions" v-model="currentPlaylistId" />
                     </div>
                     <div class="buttons-row">
                       <c-button v-show="playlists.length > 0" color="green" icon-only @click="showPlaylistEditPanel()" icon="edit"></c-button>
                      <c-button v-show="playlists.length > 0" color="red" icon-only @click="deleteCurrentPlaylist()" icon="delete"></c-button>
                     </div>
                   </div>
                 </div>
                 <div class="radio-scheduler__playlists__items">
                   <c-preloader block  v-show="playlistloading" />
                   <c-nothing-found :title="$t('dashboard.tracks.no_playlists._title')" :text="$t('dashboard.tracks.no_playlists._text')" v-if="playlists.length === 0"/>
                   <div class="radio-scheduler__table" v-dragula="playlistTracksList" drake="tracks">
                     <div class="audio-track" :data-info="JSON.stringify({inPlaylist:true,index:$index,track:track})"  :class="{'audio-track--selected':track._selected}" :key="track.id" v-for="(track,$index) in playlistTracksList">
                       <div v-show="track.is_folder" class="audio-track__col audio-track__col--icons">
                         <div v-show="!track.is_folder && false" class="audio-track__checkbox">
                           <c-checkbox v-model="track._selected" @click="selectTrack(track,true)" />
                         </div>
                         <div v-if="track.is_folder" class="audio-track__folder-icon-container">
                           <i class="material-icons">folder_open</i>
                         </div>
                       </div>
                       <div class="audio-track__col audio-track__col--main" @click="selectTrack(track,false)">
                         <div class="audio-track__inner">
                           <span :style="{width:(track._uploadProgress * 100)+'%'}" v-if="track.upload_status === 'STATUS_UPLOADING'" class="audio-track__upload-progress"></span>
                           <div class="audio-track__inner">
                             <div class="audio-track__info-block" v-if="track.title">
                               <span class="audio-track__title">{{track.title}}</span>
                               <span class="audio-track__artist">{{track.author}}</span>
                             </div>
                             <span class="audio-track__filename" v-else-if="track.filename">{{track.filename}}</span>
                             <span class="audio-track__filename" v-else-if="track.is_folder">{{track.folder_title}}</span>
                           </div>
                         </div>
                       </div>
                       <div class="audio-track__col audio-track__col--actions">
                         <div class="audio-track__buttons">
                           <div class="buttons-row">
                             <c-button flat rounded icon="delete" icon-only @click="deleteTrackFromCurrentPlaylist(track)"></c-button>
                           </div>
                         </div>
                       </div>
                     </div>
                   </div>
                 </div>
                 <div class="radio-scheduler__playlists__bottom-panel" v-show="currentPlaylistId >= 0">
                   <c-button v-show="playlistNeedsSave" :loading="playlistSaving.state" icon="save" @click="savePlaylist()" color="green">{{$t('global.save')}}</c-button>
                   <span v-show="playlistNeedsSave && !playlistSaving.state"  class="radio-scheduler__playlists__bottom-panel__text">{{$t('dashboard.tracks.unsaved_changes')}}</span>
                   <c-preloader v-if="loadingInBackground"></c-preloader>
                   <div v-if="playlistSaving.state" class="radio-scheduler__loading-bar">
                     <div class="radio-scheduler__loading-bar__inner" :style="{width: playlistSaving.percent * 100 + '%'}"></div>
                   </div>
                 </div>
               </div>
               <div class="radio-scheduler__queue" v-show="currentTab === 'queue'">
                 <div class="radio-scheduler__queue__inner" v-dragula="queue" drake="tracks">
                   <div class="audio-track audio-track--in-queue" :data-info="JSON.stringify({inQueue:true, index:$index, track: track.data})" v-if="track" :key="track.id" v-for="(track,$index) in queue">
                     <div class="audio-track__col audio-track__col--main">
                       <div class="audio-track__inner">
                         <div  class="audio-track__inner">
                           <div class="audio-track__info-block" v-if="track.data.title">
                             <span class="audio-track__title">{{track.data.title}}</span>
                             <span class="audio-track__artist">{{track.data.author}}</span>
                           </div>
                           <span class="audio-track__filename" v-else-if="track.data.filename">{{track.data.filename}}</span>
                         </div>
                       </div>
                     </div>
                     <div class="audio-track__col audio-track__col--actions">
                       <div class="audio-track__buttons">
                         <div class="buttons-row">
                           <c-button flat rounded icon="delete" icon-only @click="deleteTrackFromQueue(track)"></c-button>
                         </div>
                       </div>
                     </div>
                   </div>

                 </div>
                 <div class="radio-scheduler__queue__bottom-panel">
                   <c-button @click="saveQueue()" :loading="savingQueue" color="green">{{$t('global.save')}}</c-button>

                 </div>
               </div>
               <div class="radio-scheduler__announces" v-show="currentTab === 'announces'">
                 <div class="radio-scheduler__announces__manager-container">
                   <AnnouncesManager @deleted="onAnnounceDeleted" :broadcastingUsers="broadcastingUsers"  :playlistsList="playlistsList" :isChannelAdmin="isChannelAdmin" :canAddAnnounces="canAddAnnounces" :channel="channel" v-model="schedule.announces"/>
                 </div>
                 <div class="radio-scheduler__announces__bottom-panel">
                   <c-button v-if="canAddAnnounces" @click="openAnnouncePanel()" color="green" icon="add_box">{{$t('scheduler.add_stream_announce')}}</c-button>
                 </div>
               </div>
             </div>

                 <!--
                 <div class="list-container">
                   <div class="list-container__inner"
                     <div class="list-item list-item--small" @click="currentPlaylistId = playlist.id" :class="{'list-item--selected':currentPlaylistId === playlist.id}" :key="$index" v-for="(playlist,$index) in playlists.list">
                       <div class="list-item__left">
                         <div class="list-item__captions">
                           <a :title="playlist.title" class="list-item__title">{{playlist.title}}</a>
                         </div>
                       </div>
                       <div class="list-item__right">
                         <div class="list-item__buttons">
                           <c-button @click="currentEditing.playlist = playlist;panelsVisible.playlists=true;" flat icon-only icon="edit"></c-button>
                           <c-button @click="playlistToDelete.data = playlist;" flat icon-only icon="delete"></c-button>
                         </div>
                       </div>
                     </div>
                   </div>
                 </div> -->
           </ResizableRowChild>
          </ResizableRow>
        </div>
      </div>
    </div>
    <div class="radio-scheduler__player-container">
      <RadioPlayerPanel :channel="channel"/>
    </div>
  </div>
</template>
<style lang="scss">
    .radio-scheduler {
      display: flex;
      flex-direction: column;
      height: 100%;
      position: relative;
      &__queue {
        height: 100%;
        display: flex;
        flex-direction: column;
        &__bottom-panel {
          background: var(--box-footer-color);
          padding: 1em;
        }
        &__inner {
          background: linear-gradient(90deg, rgba(255, 255, 255, .01176), rgba(255, 255, 255, .1098));
          padding: 1em;
          height: 100%;
        }
      }
      &__outer {
        display: flex;
        flex-direction: column;
        height: 100%;

        /*
        .modal__input-container {
          padding: 1em 0;
        }

        .modal__input-container:last-of-type {
          padding: 1em 0 0;
        }*/
      }
      &__audio-manager-container {
        height: 100%;
      }
      &__main-content {
        height: calc(100% - 6em);
      }
      &__main {
        position: relative;
        display: flex;
        flex-direction: column;
        height: calc(100% + 2em);
        overflow: hidden;
        .resizable-row {
          height: 100%;
          -webkit-box-flex: 1;
        }
      }
      &__search{
        padding: 1em;
        background: var(--box-color);
        .input__container {
          margin: 0!important;
        }
      }
      &__select-all-container {
        display: inline-block;
        margin: -.45em .25em 0;
        position: relative;
        cursor: pointer;
        &:after {
          content: "";
          display: block;
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          z-index: 1;
        }
      }
      &__loading-bar {
        flex: 1;
        height: 1em;
        border-radius: 1em;
        background: #2f2f2f;
        margin: 0 0 0 .5em;
        overflow: hidden;
        &__inner {
          height: 100%;
          background: #4caf79;
          transition: all .5s;
        }
      }
      &__tracks {
        display: flex;
        flex-direction: column;
        height: 100%;
        &__bottom-panel {
          background: var(--box-footer-color);
          padding: .5em;
        }
      }
      &__playlists-container {
        height: 100%;
        display: flex;
        flex-direction: column;
      }

      &__playlists {
        margin: 0;
        min-width: 35%;
        position: relative;
        height: calc(100% - 1.35em);
        display: flex;
        flex-direction: column;
        &__header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: .65em 1em;
          background: var(--box-footer-color);
          margin: 0;
          &__left{
            display: flex;
            align-items: center;
          }
          &__right{
            display: flex;
            align-items: center;
          }
          &__select {
            margin: 0 .5em 0 0;
          }
          &__text {
            font-size: 1.15em;
            margin: 0 .5em 0 0;
          }
        }

        &__items {
          overflow: auto;
         height: calc(100% - 5.5em);
        }
        &__bottom-panel {
          background: var(--box-footer-color);
          display: flex;
          justify-content: flex-start;
          align-items: center;
          padding: 1em;
          height: 2.25em;
          &__text {
            font-size: .875em;
            margin: 0 0 0 1em;
          }
        }
      }
      &__playlists__items &__table {
        padding: 0 1em;
      }
      &__playlists &__buttons {
        margin: 0 1em 0 0;
      }
      &__pager {
        font-size: .875em;
        position: absolute;
        bottom: 4em;
        right: 0;
        opacity: .75;
      }
      &__play-button-container {
        text-align: center;
        .button-rounded{
          width:2em;
          height:2em;
          .button__content{
            padding:0;
          }
        }
      }
      &__buttons-rows-container{
        display: flex;
        justify-content: space-between;
        align-items: center;
        .popup-menu-container {
          width: 100%;
        }
      }
      &__top-buttons {
        padding: 1em;
        background: var(--box-header-color);
        width: auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
      }
      &__buttons {
        width: 100%;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        text-align: center;
      }
      &__info {
        padding: .5em 1em .5em 0;
        margin: -1px;
        position: relative;
        cursor: pointer;
        height: 2.5em;
        display: flex;
        align-items: center;
        width: 100%;
        &__inner {
          display:flex;
          align-items:center;
          z-index: 1;
          position: relative;
        }

      }
      &__table-outer {
        height: calc(100% - 3em);
        overflow: auto;
        background: #444;
      }

      &__table-container {
        position: relative;
        width: 100%;
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
      }

      &__table {
        background: linear-gradient(90deg, rgba(255, 255, 255, .01176), rgba(255, 255, 255, .1098));
        flex: 1;
        height: 100%;
        overflow: auto;
      }
      &__table__item--folder &__info{
        padding:0;
      }

      &__table-header {
        width:100%;
        background: var(--title-box-color);
        .theme-default & {
          box-shadow: 0 5px 15px -5px #000;
        }
        position:relative;
        height:3em;
        &__item  {
          position:absolute;
          display:inline-block;
          font-weight:500;
          padding:1em;
        }
      }
      &__bottom-panel {
        background: var(--box-footer-color);
        z-index: 1;
        .theme-default & {
          box-shadow: 0 -5px 35px -5px rgba(0, 0, 0, 0.35);
        }
        padding: .5em;
        display: flex;
        align-items: center;
        justify-content: space-between;
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
        &__title {
          font-size: 2em;
          margin: 1em;
          padding: 1em;
          width: 100%;
          border: .15em dashed #fff;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: 500;
        }
      }

      &__folder-size {
        font-size: .875em;
      }
      &__announces {
        display: flex;
        flex-direction: column;
        height: 100%;

        &__bottom-panel {
          background: var(--box-footer-color);
          padding: 1em;
        }

        &__manager-container {
          flex: 1;
        }
      }
    }

</style>
<script>
  import PlaylistModal from '@/components/dashboard/tracks/playlistModal';
  import AudioManager from '@/components/AudioManager';
  import ResizableRow from '@/components/global/resizable/ResizableRow';
  import ResizableRowChild from '@/components/global/resizable/ResizableRowChild';
  import ResizableRowBar from '@/components/global/resizable/ResizableRowBar';
  import playlistEditor from '@/components/radio-scheduler/playlistEditor';
  import announcePanel from '@/components/scheduler/announcePanel';
  import AnnouncesManager from "@/components/scheduler/AnnouncesManager";
  import RadioPlayerPanel from "@/components/RadioPlayerPanel";

  async function asyncForEach(array, callback) {
    for (let index = 0; index < array.length; index++) {
      await callback(array[index], index, array)
    }
  }

  let defaultAnnounceData = {
    length: 30,
    time: Math.floor(new Date().getTime() / 1000),
    title: '',
    description: '',
    user_id: null,
    cover: null,
    tags: [],
  };

  let defaultPlaylistData = {
    title: '',
    description: '',
    playback_weight:5,
    playback_type:0,
    playback_order:0,
    is_visible:false,
    cover: '',
  };
  let defaultPlaylistAdditionalData = {
    tracks_between:5,
    minutes_between:60,
    tracks_count: 1,
    play_times: {},
    play_times_equal:true,
    play_times_equal_from:'12:00',
    play_times_equal_till:'15:00',
    play_times_equal_in:'12:00',
    custom_time_start: '',
    custom_time_end: '',
  };

  export default {
    head () {
      return {
        title: this.$t('scheduler._title'),
      }
    },
    middleware: 'auth',
    async asyncData({app, params, redirect}) {
      let channelData = (await app.$api.get(`/channels/${params.id}?do_not_count_stat=1`));
      if (channelData.status) {
        if (!channelData.data.is_radio) {
          return redirect(`/scheduler/${params.id}`);
        }
        let permissions = (await app.$axios.post(`/channels/${params.id}/getpermissions`)).data;
        if (permissions.length > 0) {
          let playlists = (await app.$axios.post(`/radioplaylists/getbychannel/${params.id}`)).data.data.list;
          let queue = (await app.$api.get(`/tracks/queue/${params.id}`)).data.list;
          let tracksData = (await app.$axios.post(`/tracks/getbychannel/${params.id}`)).data.data;
          let schedule = (await app.$api.get(`/timetable/getbychannel/${params.id}/all`));
          let tracks = tracksData.list;
          let folders = tracksData.folders;
          let currentPlaylistId = -1;
          let foldersCache = {};
          foldersCache[-1] = folders;
          return {
            queue,
            permissions,
            schedule: schedule.data,
            channel: channelData.data,
            foldersCache,
            tracks,
            folders,
            playlists,
            currentPlaylistId
          };
        } else {
          return {
            error: {
              text: 'errors.403'
            }
          };
        }
      } else {
        return {
          error: channelData
        }
      }
    },
    components: {
      RadioPlayerPanel,
      announcePanel,
      AnnouncesManager,
      playlistEditor,
      PlaylistModal,
      AudioManager,
      ResizableRow,
      ResizableRowChild,
      ResizableRowBar
    },
    props: {

    },
    computed: {
      tabs() {
        return [
          {id: 'playlists', name: this.$t('scheduler.tabs.playlists')},
          {id: 'queue', name: this.$t('scheduler.tabs.queue')},
          {id: 'announces', name: this.$t('scheduler.tabs.announces')},
        ]
      },
      canAddAnnounces() {
        let canAdd = false;
        ['channel_admin', 'admin', 'owner', 'live_broadcast'].forEach(key => {
          if (this.permissions.indexOf(key) !== -1) {
            canAdd = true;
          }
        });
        return canAdd;
      },
      projectsList() {
        let data = this.projects.map(project => {
          return {
            name: project.name,
            value: project.id
          }
        });
        data.unshift({
          name: '...',
          value: null
        });
        return data;
      },
      isChannelAdmin() {
        let isAdmin = false;
        ['channel_admin', 'admin', 'owner'].forEach(key => {
          if (this.permissions.indexOf(key) !== -1) {
            isAdmin = true;
          }
        });
        return isAdmin;
      },
      idsInPlaylist() {
        return this.playlistTracks.list.map(track=>track.id);
      },
      playlistsOptions() {
        return this.playlists.map(playlist=> {
          return {name: playlist.title, value: playlist.id};
        });
      },
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
      playlistsToMoveTo() {
        let playlists = this.playlists.filter(playlist => playlist.id !== this.currentPlaylistId);
        return playlists;
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
      deletePlaylistMoveTo() {
        let moveVariants = this.playlists.filter(playlist=>playlist.id !== this.playlistToDelete.data.id).map(playlist=>{
          return {
            value:playlist.id,
            name:playlist.title
          }
        });
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
      deletePlaylistActions() {
        let actions = [
          {value:0,name:this.$t('dashboard.tracks.playlists.delete_playlist.with_tracks')},
        ];
        if (this.deletePlaylistMoveTo.length > 0) {
          actions.push(
            {value:1,name:this.$t('dashboard.tracks.playlists.delete_playlist.move_tracks')},
          );
        }
        return actions;
      },
      playlistTracksList: {
        get() {
          let allTracks = this.playlistTracks;
          if (!allTracks) {
            return [];
          }
          allTracks = allTracks.list.map(track=>{
            track._selected = this.selectedPlaylistTracks.indexOf(track) !== -1;
            return track;
          });
          return allTracks;
        },
        set: function (newVal) {
          console.log(newVal);
        }
      },
      tracksList() {

        let allTracks = [];
        if (this.search.input && this.search.input !== "" && this.search.results) {
          allTracks = this.search.results.data;
        } else {
          let tracks = this.tracks.data;
          let loading = this.uploadingFilesList.filter(track=>track.folder_id === this.currentFolderId);
          allTracks = tracks.concat(loading);
        }

        allTracks = allTracks.map(track=>{
          track._selected = this.selectedTracks.indexOf(track) !== -1;
          return track;
        }).sort((a, b)=>{
          return (a.is_folder === b.is_folder)? 0 : a.is_folder ? -1 : 1;
        });
        if (this.currentFolderId !== -1 && !this.loading) {
          allTracks.unshift({
            _selected:false,
            is_folder:true,
            _is_back_folder:true,
            id:-1,
          })
        }
        return allTracks;
      },
    },
    data() {
      return {
        savingQueue: false,
        playlistDeletePanel: {
          loading: false,
          visible: false,
        },
        announcePanel: {
          response: null,
          visible: false,
          loading: false,
          isEditing: false,
          data: defaultAnnounceData,
          coverData: null,
          errors: {

          }
        },
        currentTab: 'playlists',
        projects: [],
        broadcastingUsers: [],
        drakeName: 'main',
        playlistTracks: {
          loading: false,
          list: []
        },
        currentPlaylist: null,
        moveToFolderPanel: {
          id: -1,
          visible: false,
        },
        currentPage: 1,
        trackEditPanel: {
          visible: false,
          data: null
        },
        search: {
          input: '',
          loading: false,
          results: null,
        },
        isResizing: false,
        addFolder: {
          visible: false,
          name: '',
        },
        playlistToDelete: {
          data: null,
          variant: 0,
          move_to: -1,
        },
        folderToDelete: {
          data: null,
          variant: 0,
          move_to: -1,
        },
        playlistPanel: {
          data: null,
          visible: false
        },
        loadingInBackground: false,
        loading: false,
        playlistloading: false,
        newPlaylistData: {},
        selectedTracks: [],
        selectedPlaylistTracks: [],
        parentsCache: {},
        currentFolder: null,
        currentFolderId: -1,
        uploadingFilesList: [],
        loadingFiles: false,
        isDragging: false,
        trackTableColWidth: [],
        filesWithNameConflict: [],
        tracksCache: {},
        playlistTracksCache: {},
        selectAllHandler: false,
        playlistNeedsSave: false,
        playlistSaving: {
          state: false,
          percent: 0
        }
      }
    },
    watch: {
      currentPage(newPage, oldPage) {
        this.setTracksCache(this.tracks, oldPage, this.currentFolderId);
        this.setFoldersCache(this.folders, oldPage);
        this.getTracks();
      },
      "playlistTracks.list": function() {
        this.playlistNeedsSave = true;
      },
      selectedTracks(tracksList) {
        this.selectAllHandler = (tracksList.length === this.tracks.data.filter(track=>!track.is_folder).length);
      },
      isResizing(resizing) {
        if (!resizing) {
          this.onFilesListChange();
        }
      },
      currentPlaylistId(newId, oldId) {
        this.drakeName = "playlist_"+newId;
        localStorage.currentPlaylistId = newId;
        this.search.input = "";
        this.setPlaylistTracksCache(this.tracks,oldId);
        this.currentFolderId = -1;
        this.getPlaylistTracks();
        return null;
        //console.log(this.drakeName);

      },
      currentFolderId(newId,oldId) {
        this.search.input = "";
        this.setTracksCache(this.tracks, this.currentPage, oldId);
        this.setFoldersCache(this.folders, this.currentPage);
        this.getTracks();
      }
    },
    created() {
        const service = this.$dragula.$service;
        service.eventBus.$on('drop', (args) => {
          const sourceIsTracksList = args.source.classList.contains('audio-manager__tracks');
          const targetIsTracksList = args.container.classList.contains('audio-manager__tracks');
          const sourceIsQueue = args.source.classList.contains('radio-scheduler__queue__inner');
          const targetIsQueue = args.container.classList.contains('radio-scheduler__queue__inner');
          const el = args.el;
          if (!sourceIsTracksList && !targetIsTracksList && !sourceIsQueue && !targetIsQueue) {
            this.playlistNeedsSave = true;
          }
          if (sourceIsTracksList && targetIsQueue) {
            console.log('queue', args);
            const index = Array.prototype.indexOf.call(el.parentElement.children, el);
            const trackData = JSON.parse(el.dataset.info);
            const track = trackData.track;
            el.parentNode.removeChild(el);
            let data = {
              data: track
            };
            this.queue.splice(index, 0, data);
          } else {
            if (sourceIsTracksList && !targetIsTracksList) {
              if (el.parentElement) {
                const index = Array.prototype.indexOf.call(el.parentElement.children, el);
                const trackData = JSON.parse(el.dataset.info);
                const track = trackData.track;
                el.parentNode.removeChild(el);
                if (this.idsInPlaylist.indexOf(track.id) === -1) {
                  if (track._is_back_folder) return;
                  this.playlistTracks.list.splice(index, 0, track);
                }
              } else {
                console.log(el);
              }
            }
          }
        });
        service.options('tracks', {
          moves: function (el, container, handle) {
            return !handle.classList.contains('audio-track__col--main');
          },
          copy: function(el,source) {
            const sourceIsTracksList = source.classList.contains('audio-manager__tracks');
            return sourceIsTracksList;
          },
          accepts: function (el, target, source, sibling) {
            const sourceIsTracksList = source.classList.contains('audio-manager__tracks');
            const targetIsTracksList = target.classList.contains('audio-manager__tracks');
            if (sourceIsTracksList && !targetIsTracksList) {
              return true;
            }
            if (!sourceIsTracksList && !targetIsTracksList) {
              return true;
            }
            return false;
          },
          revertOnSpill: true
        })
    },
    async mounted() {
      if (this.playlists.length > 0) {
        this.currentPlaylistId = this.playlists[0].id;
        this.currentPlaylist = this.playlists[0];
        this.getPlaylistTracks();
      }
      this.onFilesListChange();

      this.projects = (await this.$axios.post(`/projects/getbychannel/${this.channel.id}`)).data.list.data;
      if (this.isChannelAdmin) {
        let team = (await this.$axios.post(`/channels/${this.channel.id}/team/get`, {
          types: ['channel_admin', 'admin', 'owner', 'broadcaster']
        })).data.list;
        let users = team.filter(item => item.user_id !== this.$store.state.userData.id).map(item => {
          return {
            name: item.username,
            value: item.user_id
          }
        });
        users.unshift({
          name: `${this.$t('scheduler.you')} (${this.$store.state.userData.username})`,
          value: this.$store.state.userData.id
        });
        this.broadcastingUsers = users;
      }
      this.$echo.join(`App.Channel.${this.channel.id}`)
        .listen('.channel.new_radio_track', (e) => {
          if (e.data.is_queue) {
            this.queue = this.queue.filter(track => track.queue_track_id !== e.data.queue_track_id);
          }
        })

    },
    methods: {
      saveQueue() {
        this.savingQueue = true;
        this.$axios.post(`/tracks/queue/${this.channel.id}`, {queue: this.queue}).then(res => {
          this.savingQueue = false;
          if (!res.data.status) {
            this.$store.commit('NEW_ALERT',res.data$);
          } else {
            this.queue = res.data.data.list;
          }
        })
      },
      continueDeletingCurrentPlaylist() {
        this.playlistDeletePanel.loading = true;
        this.$axios.delete('/radioplaylists/'+this.currentPlaylist.id).then(res=>{
          this.playlistDeletePanel.loading = false;
          this.$store.commit('NEW_ALERT',res.data);
          if (res.data.status) {
            this.playlistDeletePanel.visible = false;
            this.playlists = this.playlists.filter(playlist => playlist.id !== this.currentPlaylist.id);
            if (this.playlists.length > 0) {
              this.currentPlaylistId = this.playlists[0].id;
              this.currentPlaylist = this.playlists[0];
            }
          }
        });
      },
      deleteCurrentPlaylist() {
        this.playlistDeletePanel.visible = true;
      },
      openAnnouncePanel() {
        this.announcePanel.isEditing = false;
        this.announcePanel.data = JSON.parse(JSON.stringify(defaultAnnounceData));
        this.announcePanel.visible = true;
      },
      onAnnounceDeleted(deletedItem) {
        this.schedule.announces = this.schedule.announces.filter(announce => announce.id !== deletedItem.id);
      },
      onAnnounceSaved(newAnnounce) {
        if (this.announcePanel.isEditing) {
          this.schedule.announces = this.schedule.announces.filter(announce => announce.id !== newAnnounce.id);
        }
        this.schedule.announces.unshift(newAnnounce);
      },
      onMoveToPlaylist(selectedTracks) {
        const idsInPlaylist = this.playlistTracksList.map(track => track.id);
        let tracks = selectedTracks.filter(track => idsInPlaylist.indexOf(track.id) === -1);
        tracks = tracks.map(track => Object.assign({}, track));
        this.playlistTracks.list = this.playlistTracks.list.concat(tracks);
      },
      deleteTrackFromCurrentPlaylist(track) {
        this.playlistTracksList.splice(this.playlistTracksList.indexOf(track, 1));
        this.playlistTracks.list.splice(this.playlistTracks.list.map(trackItem => trackItem.id).indexOf(track.id), 1);
      },
      moveToFolder() {
        this.batchAction('move_to_folder', this.moveToFolderPanel.id, this.currentFolderId);
      },
      deleteTracksFromPlaylist() {
        const selectedIds = this.selectedPlaylistTracks.map(track => track.id);
        this.playlistTracks.list = this.playlistTracks.list.filter(track=>selectedIds.indexOf(track.id) === -1);
      },
      showPlaylistAddPanel() {
        let data = defaultPlaylistData;
        this.playlistPanel.data = JSON.parse(JSON.stringify(data));
        this.playlistPanel.visible = true;
      },
      showPlaylistEditPanel() {
        this.currentPlaylist = this.playlists.filter(playlist => playlist.id === this.currentPlaylistId)[0];
        this.playlistPanel.data = JSON.parse(JSON.stringify(this.currentPlaylist));
        this.playlistPanel.visible = true;
      },
      savePlaylist() {
        this.playlistSaving.state = true;
        this.playlistSaving.percent = 0;
        const tracks_in_part = 20;
        let idsList = this.playlistTracksList.map(track => {
          return {
            id: track.id,
            is_folder: track.is_folder
          }
        });
        let tempLists = [];
        let lists = [];
        while (idsList.length) {
          tempLists.push(idsList.splice(0, tracks_in_part));
        }
        tempLists.forEach((list, index)=>{
          lists[index] = {};
          list.forEach((item, i) => {
            lists[index][i + 1 + (index * tracks_in_part)] = item;
          });
        });
        let i = 0;
        let has_errors = false;
        asyncForEach(lists, async (list) => {
          if (!has_errors) {
            const res = await this.$axios.post('/radioplaylists/' + this.currentPlaylistId + '/batchsave', {data: list});
            if (res.data.status) {
              this.playlistSaving.percent = (i + 1) / lists.length;
              i++;
              if (i === lists.length) {
                this.playlistNeedsSave = false;
                this.playlistSaving.state = false;
              }
            } else {
              this.$store.commit('NEW_ALERT', res.data);
              has_errors = true;
            }
          }
        });
      },
      addSelectedToPlaylist() {
        const idsInPlaylist = this.playlistTracksList.map(track => track.id);
        let tracks = this.selectedTracks.filter(track => idsInPlaylist.indexOf(track.id) === -1);
        tracks = tracks.map(track => Object.assign({}, track));
        this.playlistTracks.list = this.playlistTracks.list.concat(tracks);
        this.selectedTracks = [];
      },
      goSearch() {
        this.reloadTracks();
      },
      reloadTracks() {
        this.removeTracksCache(this.currentFolderId, this.currentPage);
        this.getTracks();
      },
      reloadPlaylistTracks() {
        this.removePlaylistTracksCache(this.currentPlaylistId);
        this.getPlaylistTracks();
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
            if (isPlaylistsAction) {
              this.selectedPlaylistTracks = [];
              this.playlistTracks.list = this.playlistTracks.list.filter(track=>ids.indexOf(track.id) === -1);
              this.setPlaylistTracksCache(this.tracks,this.currentPlaylistId);
            } else {
              this.selectedTracks = [];
              this.tracks.data = this.tracks.data.filter(track => ids.indexOf(track.id) === -1);
              this.setTracksCache(this.tracks, this.currentPage, this.currentFolderId);
            }


            // }
          }
        })
      },
      selectAll() {
        if (!this.selectAllHandler) {
          this.selectedTracks = this.tracks.list.filter(track=>!track.is_folder);
        } else {
          this.selectedTracks = [];
        }
      },
      removePlaylistTracksCache(playlistId) {
        if (!this.playlistTracksCache[playlistId]) {
          return true;
        }
        this.playlistTracksCache[playlistId] = null;
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
      editTrack(track) {

      },
      playTrack(track) {
        let data = {url:track.client_path,author:""};
        if (track.title) {
          data.title = track.title;
          data.author = track.author || "";
        } else {
          data.title = track.filename;
        }
        this.$store.commit("SET_PLAYER_TRACK",data);
      },
      editFolder(editedFolder) {
        folder.is_folder = true;
        this.tracks.list.forEach((track,i)=>{
          if (track.id === editedFolder.id) {
            this.$set(this.tracks.list,i,editedFolder);
          }
        })
      },
      newFolder(folder) {
        folder.is_folder = true;
        this.tracks.data.push(folder);
      },
      deleteFolder() {
        let data = this.folderToDelete.action === 0 ? {delete_tracks:true} : {move_tracks_to:this.folderToDelete.move_to};
        this.$axios.delete('/folders/'+this.folderToDelete.data.id,{data}).then(res=>{
          this.$store.commit('NEW_ALERT',res.data);
          if (res.data.status) {
            if (this.currentFolderId === this.folderToDelete.data.id) {
              this.currentFolderId = this.folderToDelete.data.parent_id;
            }
            this.folders = this.folders.filter(folder => folder.id !== this.folderToDelete.data.id)
            this.tracks.data = this.tracks.data.filter(track => (!track.is_folder || (track.is_folder && track.id !== this.folderToDelete.data.id)));
            this.folderToDelete.data = null;
          }
        });
      },
      deletePlaylist() {
        let data = this.playlistToDelete.action === 0 ? {delete_tracks:true} : {move_tracks_to:this.playlistToDelete.move_to};
        this.$axios.delete('/radioplaylists/'+this.playlistToDelete.data.id,{data}).then(res=>{
          this.$store.commit('NEW_ALERT',res.data);
          if (res.data.status) {
            if (this.playlistToDelete.data.id === this.currentPlaylistId) {
              let index = this.playlists.indexOf(this.playlistToDelete.data);
              if (this.playlists.length > 1) {
                this.playlists.splice(index,1);
                if (index>0) {
                  index--;
                } else {
                  index++;
                }
                this.currentPlaylistId = this.playlists[index].id;
              }
            }
            this.playlistToDelete.data = null;
          }
        });
      },
      editPlaylist(editedPlaylist) {
        this.playlists.forEach((playlist,i)=>{
          if (playlist.id === editedPlaylist.id) {
            this.$set(this.playlists,i,editedPlaylist);
          }
        })
      },
      addPlaylist(playlist) {
        this.playlists.unshift(playlist);
        this.currentPlaylistId = playlist.id;
        this.currentPlaylist = playlist;
      },
      getPlaylistTracks() {
        //console.log(this.playlistTracksCache);
        if (this.playlistTracksCache[this.currentPlaylistId]) {
          this.playlistTracksCache = this.tracksCache[this.currentPlaylistId];
          this.onFilesListChange();
        } else {
          this.playlistloading = true;
          let data = {};
          if (this.search.input) {
            data.search = this.search.input;
          }
          this.$axios.post(`/tracks/getbyplaylist/${this.currentPlaylistId}`, data).then(res => {
            this.playlistloading = false;
            if (res.data.status) {
              this.playlistTracks.list = res.data.data.list;
              this.onFilesListChange();
              this.$nextTick(() => {
                this.playlistNeedsSave = false;
              })
            } else {
              this.$store.commit('NEW_ALERT', res.data);
            }
          });
        }
      },
      getTracks() {
        if (this.tracksCache[this.currentFolderId] && this.tracksCache[this.currentFolderId][this.currentPage]) {
          this.tracks = this.tracksCache[this.currentFolderId][this.currentPage];
          if (this.foldersCache[this.currentFolderId]) {
            this.folders = this.foldersCache[this.currentFolderId];
          }
          this.onFilesListChange();
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
              }
              this.onFilesListChange();
            } else {
              this.$store.commit('NEW_ALERT', res.data);
            }
          });
        }
      },
      conflictKeepBoth(file) {
        this.filesWithNameConflict.splice(this.filesWithNameConflict.indexOf(file,1));
        let hasTrackWithSuchName = true;
        let newFileName = "";
        let i = 1;
        while (hasTrackWithSuchName) {
          newFileName = '('+i+') '+file.title;
          hasTrackWithSuchName = this.tracks.data.map(track=>track.filename).indexOf(newFileName) !== -1;
          i++;
        }
        file.file._name = newFileName;
        this.uploadFile(file.file);
      },
      conflictSkipFileAll() {
        this.filesWithNameConflict = [];
      },
      conflictSkipFile(file) {
        this.filesWithNameConflict.splice(this.filesWithNameConflict.indexOf(file,1));
      },
      selectTrack(track,isTracksPanel) {
        let selectedTracks = isTracksPanel ? this.selectedTracks : this.selectedPlaylistTracks;
        console.log(selectedTracks);
        if (!track.is_folder) {
          if (selectedTracks.indexOf(track) !== -1) {
            track._selected = false;
            selectedTracks.splice(selectedTracks.indexOf(track),1);
          } else {
            track._selected = true;
            selectedTracks.push(track);
          }
        } else {
          if (isTracksPanel) {
            if (track._is_back_folder) {
              if (this.parentsCache[this.currentFolderId]) {
                this.currentFolderId = this.parentsCache[this.currentFolderId];
              } else {
                this.currentFolderId = this.currentFolder.parent_id;
              }

            } else {
              this.parentsCache[track.id] = this.currentFolderId;
              this.currentFolder = track;
              this.currentFolderId = track.id;
            }
          }
        }
      },
      deleteTrack(track) {
        if (track.is_folder) {
          this.folderToDelete.data = track;
        } else {
          this.$axios.delete('/tracks/' + track.id).then(res => {
            this.$store.commit('NEW_ALERT', res.data);
            if (res.data.status) {
              this.tracks.data.splice(this.tracks.data.indexOf(track), 1);
              this.playlistTracks.list = this.playlistTracks.list.filter(playlistTrack=>playlistTrack.id !== track.id);
              this.onFilesListChange();
            }
          })
        }
      },
      onFilesListChange() {
        return null;
        this.$nextTick(()=>{
          //let maxWidth = 0;
          if (this.tracksList.length > 0) {
            let table = document.getElementsByClassName('radio-scheduler__table--main');
            let tr = table[0].children[0];
            for (let i = 0; i < 3; ++i) {
              let width = tr ? tr.children[i].offsetWidth : '';
              this.$set(this.trackTableColWidth, i, parseInt(width));
            }
          }
        })
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
      uploadFile(track) {
        let file = track._file;
        let fileName = file.name;
        let hasTrackWithSuchName = this.tracks.data.map(track=>track.filename).indexOf(fileName) !== -1;
        //console.log(hasTrackWithSuchName);
        //this.isUploading = true;
        if (hasTrackWithSuchName) {
          this.filesWithNameConflict.push({
            title:file.name,
            status:-1,
            file,
            existingItem: this.tracks.data.filter(track=>track.filename == fileName)[0],
          });
        } else {
          let fd = new FormData();
          fd.append('track',file);
          fd.append('folder_id',track.folder_id);
          fd.append('playlist_id',this.currentPlaylistId);
          fd.append('channel_id',this.channel.id);

          this.onFilesListChange();
          track.upload_status = "STATUS_UPLOADING";
          this.$axios.post('/tracks',fd,{onUploadProgress: progressEvent => {
              track._uploadProgress = ((progressEvent.loaded) / progressEvent.total);
            }}).then(res=>{
            this.uploadingFilesList.splice(this.uploadingFilesList.indexOf(track),1);
            this.startUpload();
            if (res.data.status) {
              this.tracks.data.push(res.data.track);
            } else {
              this.$store.commit('NEW_ALERT',res.data);
            }
          })
        }
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
      }
    }
  }
</script>
