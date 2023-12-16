<template>
  <div>
    <c-modal v-if="currentPlaylist" class="scheduler__playlist-contents" v-model="isVisible">
      <div slot="main">
        <div class="scheduler__playlist-contents__header">
          <div class="scheduler__playlist-contents__header__input-container">
            <div class="row">
              <div class="col">
                <c-input :title="$t('scheduler.playlist_title')" v-model="currentPlaylist.data.title" />
              </div>
              <div class="col col--button-container">
                <c-colorpicker :title="$t('scheduler.playlist_color')"  v-model="currentPlaylist.data.color"/>
              </div>
            </div>
          </div>
          <div class="scheduler__playlist-contents__types">
            <c-radio-buttons :inline="true" :values="playlistTypes" v-model="currentPlaylist.data.type" />
          </div>
          <div class="scheduler__playlist-contents__header__input-container">
            <c-select :title="$t('scheduler.playback_type')" :options="playbackTypes" v-model="currentPlaylist.data.playback_type" />
          </div>
          <div v-if="currentPlaylist.data.playback_type === 'default'" class="scheduler__playlist-contents__header__input-container">
            <c-datetime-picker :errors="errors.time_start" :minDate="0" :dialogTitle="$t('scheduler.time_start')" :title="$t('scheduler.time_start')" v-model="currentPlaylist.data.time_start" />
          </div>
          <div class="row row--centered"  v-if="currentPlaylist.data.playback_type === 'repeating'" >
            <div class="col">
              <div class="scheduler__playlist-contents__header__input-container">
                <c-time-picker :errors="errors.playback_time_start" :title="$t('scheduler.start_time')" v-model="currentPlaylist.data.playback_time_start" />
              </div>
            </div>
            <div class="col col-3-5">
              <div class="row">
                <div :key="$index" class="col" v-for="(day, $index) in currentPlaylist.data.playback_days">
                  <c-checkbox :title="$t('weekdays_short')[$index]" v-model="currentPlaylist.data.playback_days[$index]"/>
                </div>
              </div>
            </div>
          </div>
          <div class="row row--centered"  v-if="currentPlaylist.data.type === 'mixer' && currentPlaylist.data.playback_type !== 'around_the_clock'" >
            <div class="col">
              <div class="scheduler__playlist-contents__header__input-container">
                <c-select  @change="onPlaybackLimitTypeChange" :title="$t('scheduler.playback_limits._title')" :options="playbackLimitTypes" v-model="currentPlaylist.data.playback_limit.type" />
              </div>
            </div>
            <div class="col">
              <div class="scheduler__playlist-contents__header__input-container">
                <c-input :errors="errors.playback_limit_value" :title="$t('scheduler.playback_limits.titles.by_number')" v-if="currentPlaylist.data.playback_limit.type === 'by_number'" type="number" v-model="currentPlaylist.data.playback_limit.value" />
                <c-input :errors="errors.playback_limit_value" :title="$t('scheduler.playback_limits.titles.by_minutes')" v-else-if="currentPlaylist.data.playback_limit.type === 'by_minutes'" type="number" v-model="currentPlaylist.data.playback_limit.value" />
                <c-datetime-picker :errors="errors.playback_limit_value" :title="$t('scheduler.playback_limits.titles.by_date')" v-else-if="currentPlaylist.data.playback_limit.type === 'by_date'" v-model="currentPlaylist.data.playback_limit.value" />
                <c-time-picker :errors="errors.playback_limit_value"  :title="$t('scheduler.playback_limits.titles.by_time')" v-else-if="currentPlaylist.data.playback_limit.type === 'by_time'" v-model="currentPlaylist.data.playback_limit.value" />
              </div>
            </div>
          </div>
          <div  v-if="currentPlaylist.data.type === 'default' && currentPlaylist.data.playback_type === 'default'" class="row row--centered">
            <div class="col">
              <div class="scheduler__playlist-contents__header__input-container">
                <c-checkbox :title="$t('scheduler.cycled')" v-model="currentPlaylist.data.cycled.status" />
              </div>
            </div>
            <div class="col" v-if="currentPlaylist.data.cycled.status">
              <div class="scheduler__playlist-contents__header__input-container">
                <c-datetime-picker :errors="errors.cycled_till" :minDate="currentPlaylist.data.time_start" :dialogTitle="$t('scheduler.cycled_till')" :title="$t('scheduler.cycled_till')" v-model="currentPlaylist.data.cycled.till" />
              </div>
            </div>
          </div>
        </div>
        <div class="scheduler__playlist-contents__header__input-container">
          <c-select :title="$t('scheduler.playback_trim_type')" :options="playbackTrimTypes" v-model="currentPlaylist.data.playback_trim_type" />
        </div>
        <div @click="playlistItemsPanel.visible = true" class="scheduler__playlist-contents__items-button">
          <span class="scheduler__playlist-contents__items-button__text">
            <strong>{{currentPlaylist.items.length}}</strong> {{$t('scheduler.items_in_playlist')}}
          </span>
        </div>
      </div>
      <div slot="buttons">
        <div class="buttons-row">
          <c-button @click="savePlaylist()" :loading="savingPlaylist">{{$t('global.save')}}</c-button>
          <c-button v-if="currentPlaylist && currentPlaylist.id" color="red" @click="deletePlaylistPanel.visible = true">{{$t('global.delete')}}</c-button>
        </div>
      </div>
    </c-modal>
    <c-modal v-model="deletePlaylistPanel.visible">
      <div slot="main" >
        <div class="modal__text">
          {{$t('scheduler.delete_playlist_text')}}
        </div>
      </div>
      <div class="modal__buttons" slot="buttons">
        <div class="buttons-row">
          <c-button @click="deletePlaylist()" :loading="deletePlaylistPanel.loading">{{$t('global.ok')}}</c-button>
          <c-button flat @click="deletePlaylistPanel.visible = false">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>

    <c-modal class="scheduler__playlist-contents__items__modal" v-model="playlistItemsPanel.visible" v-if="currentPlaylist">
      <playlistPickerAdvanced @save="onPlaylistItemsSave" :channel="channel" :playlist="currentPlaylist" v-model="currentPlaylist.items"/>
    </c-modal>
  </div>
</template>
<script>
  import playlistPickerAdvanced from '@/components/scheduler/PlaylistPickerAdvanced';
  export default {
    components: {
      playlistPickerAdvanced
    },
    computed: {
      playbackLimitTypes() {
        let types =  [
          {value: 'by_number', name: this.$t('scheduler.playback_limits.types.by_number')},
          {value: 'by_minutes', name: this.$t('scheduler.playback_limits.types.by_minutes')},
        ];
        if (this.currentPlaylist.data.playback_type === 'default') {
          types.push(
            {value: 'by_date', name: this.$t('scheduler.playback_limits.types.by_date')}
          );
        } else {
          types.push(
            {value: 'by_time', name: this.$t('scheduler.playback_limits.types.by_time')}
          );
        }
        return types;
      },
      playbackTrimTypes() {
        return [
          {value: 'default', name: this.$t('scheduler.playback_trim_types.default')},
          {value: 'move_next', name: this.$t('scheduler.playback_trim_types.move_next')},
          {value: 'limit_prev', name: this.$t('scheduler.playback_trim_types.limit_prev')},
        ]
      },
      playbackTypes() {
        return [
          {value: 'default', name: this.$t('scheduler.playback_types.default')},
          {value: 'repeating', name: this.$t('scheduler.playback_types.repeating')},
          {value: 'around_the_clock', name: this.$t('scheduler.playback_types.around_the_clock')},
        ]
      },
      playlistTypes() {
        return [
          {id: 'default', title: this.$t('scheduler.playlist_types.default')},
          {id: 'mixer', title: this.$t('scheduler.playlist_types.mixer')},
        ]
      },
    },
    data() {
      return {
        isVisible: this.visible,
        deletePlaylistPanel: {
          visible: false,
          loading: false,
        },
        playlistItemsPanel: {
          visible: false,
        },
        errors: {},
        currentPlaylist: null,
        savingPlaylist: false,
      }
    },
    mounted() {

    },
    props: {
      visible: {
        type: Boolean,
        required: true
      },
      channel: {
        type: Object,
        required: true
      },
      value: {

      }
    },
    methods: {
      onPlaylistItemsSave() {
        this.playlistItemsPanel.visible = false;
      },
      deletePlaylist() {
        this.deletePlaylistPanel.loading = true;
        this.$axios.delete(`timetable/playlists/${this.currentPlaylist.id}`).then (res => {
          this.deletePlaylistPanel.loading = false;
          this.$store.commit('NEW_ALERT', res.data);
          if (res.data.status) {
            this.$emit('deleted', {id: this.currentPlaylist.id});
          }
        })
      },
      onPlaybackLimitTypeChange() {
        const newType = this.currentPlaylist.data.playback_limit.type;
        if (newType === "by_number") {
          this.currentPlaylist.data.playback_limit.value = 5;
        }
        if (newType === "by_minutes") {
          this.currentPlaylist.data.playback_limit.value = 30;
        }
        if (newType === "by_date") {
          this.currentPlaylist.data.playback_limit.value = (new Date()).getTime();
        }
        if (newType === "by_time") {
          this.currentPlaylist.data.playback_limit.value = "12:00";
        }
      },
      savePlaylist() {
        const isEditing = !!this.currentPlaylist.id;
        this.savingPlaylist = true;
        let data = this.currentPlaylist;
        data.channel_id = this.channel.id;
        this.$axios({
          method: isEditing ? 'PUT' : 'POST',
          url: isEditing ? `timetable/playlists/${this.currentPlaylist.id}` : `timetable/playlists`,
          data
        }).then(res => {
          this.savingPlaylist = false;
          if (res.data.status !== undefined) {
            if (!res.data.status) {
              this.$store.commit('NEW_ALERT', res.data.text);
              this.errors = res.data.errors || {};
            } else {
              this.errors = {};
              this.currentPlaylist.id = res.data.data.playlist.id;
              res.data.data.playlist.items.forEach((item, index) => {
                this.$set(this.currentPlaylist.items[index], 'id', item.id);
                if (item.is_folder) {
                  item.items.forEach((folderItem, folderIndex) => {
                    this.$set(this.currentPlaylist.items[index].items[folderIndex], 'id', folderItem.id);
                  })
                }
              });
              this.$emit('saved', {isEditing, playlist: res.data.data.playlist});
            }
          }
        })
      },
    },
    watch: {
      visible(isVisible) {
        this.isVisible = isVisible;
        if (isVisible) {
          let playlist = JSON.parse(JSON.stringify(this.value));
          if (!playlist.data) {
            playlist.data = {};
          }
          if (!playlist.data.cycled) {
            playlist.data.cycled = {
              status: false,
              till: null
            }
          }
          if (!playlist.data.playback_limit) {
            playlist.data.playback_limit = {
              type: 'by_number',
              number: 5
            }
          }
          this.currentPlaylist = playlist;
        }
      },
      isVisible(isVisible) {
        if (!isVisible) {
          this.$emit('close');
        }
      },
      "currentPlaylist.data.playback_limit.type"(newType) {

      },
      "currentPlaylist.data.playback_type"(newPlaybackType) {
        if (newPlaybackType === 'repeating') {
          if (!this.currentPlaylist.data.playback_days) {
            this.$set(this.currentPlaylist.data, 'playback_days', {});
            for (let i = 1; i <= 7; i++) {
              this.$set(this.currentPlaylist.data.playback_days, i, true);
            }
          }
        }
      },
      currentPlaylist(newPlaylist) {
        this.$emit('input', newPlaylist);
      }
    }
  }
</script>
