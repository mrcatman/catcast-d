<template>
  <c-modal class="audio-playlist-editor" v-model="val">
    <div slot="main">
      <div class="modal__input-container">
        <c-input :errors="errors.title" :title="$t('dashboard.tracks.playlists.title')" v-model="playlist.title" />
      </div>
      <!--
      <div class="row">
        <div class="col">

        </div>
        <div class="col col--button-container">
          <c-colorpicker :title="$t('dashboard.tracks.playlists.color')"  v-model="additionalData.color"/>
        </div>
      </div>
      -->
      <!--
      <div class="modal__input-container">
        <c-input type="textarea" :errors="errors.description" :title="$t('dashboard.tracks.playlists.description')" v-model="playlist.description" />
      </div>
      -->
      <div class="modal__input-container modal__input-container--checkbox">
        <c-checkbox switch :title="$t('dashboard.tracks.playlists.can_accept_requests')" v-model="playlist.can_accept_requests" />
      </div>
      <div class="modal__input-container">
        <c-select :errors="errors.playback_weight" :title="$t('dashboard.tracks.playlists.playback_weight')" :options="playbackWeightOptions" v-model="playlist.playback_weight" />
      </div>
      <div class="modal__input-container">
        <c-select :title="$t('dashboard.tracks.playlists.playback_type')" @change="onPlaybackTypeChange" :errors="errors.playback_type" :options="playbackTypeOptions" v-model="playlist.playback_type" />
      </div>
      <div v-if="playlist.playback_type === 2 || playlist.playback_type === 5">
        <div class="modal__input-container">
          <c-checkbox switch :title="$t('dashboard.tracks.playlists.play_times_equal')" v-model="additionalData.play_times_equal" />
        </div>
        <div class="vertical-delimiter"></div>
        <div class="row row--centered">
          <div class="col">
            <div class="row row--centered" :key="i" v-for="i in 7">
              <div class="col col--auto" >
                <c-checkbox v-if="playTimesDaysActive[i] !== undefined" v-model="playTimesDaysActive[i]" :title="$t('weekdays_short')[i]"  />
              </div>
              <div class="col" v-if="!additionalData.play_times_equal">
                <div v-if="playlist.playback_type === 2" style="justify-content:flex-end" class="row-with-inputs" v-show="playTimesDaysActive[i]">
                  <span class="row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_from')}}</span>
                  <div class="row-with-inputs__element">
                    <c-time-picker :errors="errors['playback_data.play_times.'+i+'.play_from']" v-model="additionalData.play_times[i].play_from" />
                  </div>
                  <span class="row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_till')}}</span>
                  <div class="row-with-inputs__element">
                    <c-time-picker :errors="errors['playback_data.play_times.'+i+'.play_till']" v-model="additionalData.play_times[i].play_till" />
                  </div>
                </div>
                <div v-else-if="playlist.playback_type === 5" style="justify-content:flex-end" class="row-with-inputs" v-show="playTimesDaysActive[i]">
                  <span class="row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_in')}}</span>
                  <div class="row-with-inputs__element">
                    <c-time-picker :errors="errors['playback_data.play_times.'+i+'.play_in']" v-model="additionalData.play_times[i].play_in" />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col" v-if="additionalData.play_times_equal">
            <div v-if="playlist.playback_type === 2"  class="row-with-inputs">
              <span class="row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_from')}}</span>
              <div class="row-with-inputs__element">
                <c-time-picker :errors="errors['playback_data.play_times.0.play_from']"  v-model="additionalData.play_times_equal_from" />
              </div>
              <span class="row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_till')}}</span>
              <div class="row-with-inputs__element">
                <c-time-picker :errors="errors['playback_data.play_times.0.play_till']" v-model="additionalData.play_times_equal_till" />
              </div>
            </div>
            <div v-else-if="playlist.playback_type === 5"  class="row-with-inputs">
              <span class="row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_in')}}</span>
              <div class="row-with-inputs__element">
                <c-time-picker :errors="errors['playback_data.play_times.0.play_to']" v-model="additionalData.play_times_equal_in" />
              </div>
            </div>
          </div>
        </div>
        <div class="vertical-delimiter"></div>
      </div>
      <div v-else-if="playlist.playback_type === 3"  class="row-with-inputs">
        <span class="row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_every')}}</span>
        <div style="flex:1" class="row-with-inputs__element">
          <c-input :errors="errors['playback_data.minutes_between']" :min="10" :max="3600" type="number" v-model="additionalData.minutes_between" />
        </div>
        <span class="row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_every_minutes')}}</span>
      </div>
      <div v-else-if="playlist.playback_type === 4" class="modal__input-container">
       <c-input :errors="errors['playback_data.tracks_between']" type="number" :min="1" :max="1000"  :title="$t('dashboard.tracks.playlists.tracks_between')" v-model="additionalData.tracks_between" />
      </div>
      <div v-if="playlist.playback_type === 6"  class="modal__input-container">
        <c-datetime-picker :title="$t('dashboard.tracks.playlists.custom_time_start')" :dialogTitle="$t('dashboard.tracks.playlists.custom_time_start')" :errors="errors['playback_data.custom_time_start']"  v-model="additionalData.custom_time_start" />
      </div>
      <div v-if="playlist.playback_type === 6"  class="modal__input-container">
        <c-datetime-picker :title="$t('dashboard.tracks.playlists.custom_time_end')" :dialogTitle="$t('dashboard.tracks.playlists.custom_time_end')" :errors="errors['playback_data.custom_time_end']" v-model="additionalData.custom_time_end" />
      </div>
      <div class="modal__input-container">
       <c-select :errors="errors.playback_order" :title="$t('dashboard.tracks.playlists.playback_order')" :options="playbackOrderOptions" v-model="playlist.playback_order" />
      </div>
      <div class="modal__input-container" v-if="playlist.playback_order === 2">
        <c-input :title="$t('dashboard.tracks.playlists.tracks_count')" :errors="errors['playback_data.tracks_count']" type="number" :min="1" :max="50"  v-model="additionalData.tracks_count" />
      </div>
      <!--
      <div class="row row-centered">
        <div class="col">
          <div class="modal__input-container">
            <c-checkbox :errors="errors.is_visible" :description="$t('dashboard.tracks.playlists.is_visible_description')" :title="$t('dashboard.tracks.playlists.is_visible')" v-model="playlist.is_visible" />
          </div>
        </div>
        <div class="col col--auto" v-show="playlist.is_visible">
          <c-picture-uploader :title="$t('dashboard.tracks.playlists.cover')" :channelid="channel.id" folder="playlist_covers" v-model="playlist.cover" />
        </div>
      </div>
      -->
    </div>
    <div class="modal__buttons" slot="buttons">
      <div class="buttons-list">
        <c-button v-if="!isEditing" :loading="loading" @click="addPlaylist()">{{$t('dashboard.tracks.playlists.add_playlist_button')}}</c-button>
        <c-button v-else :loading="loading" @click="addPlaylist()">{{$t('dashboard.tracks.playlists.edit_playlist_button')}}</c-button>
        <c-button color="red" @click="hidePanel()">{{$t('global.cancel')}}</c-button>
      </div>
    </div>
  </c-modal>
</template>
<style lang="scss">
.audio-playlist-editor {
  .modal__input-container {
    margin: 1.75em 0 0;
    &--checkbox {
      margin: 1em 0;
    }
  }
}
</style>
<script>
let defaultPlaylistData = {
	title: '',
	description: '',
	playback_weight: 5,
	playback_type: 0,
	playback_order: 0,
	is_visible: false,
	cover: '',
};
let defaultPlaylistAdditionalData = {
	tracks_between: 5,
	minutes_between: 60,
  tracks_count: 1,
	play_times: {},
	play_times_equal: true,
	play_times_equal_from: '12:00',
	play_times_equal_till: '15:00',
	play_times_equal_in: '12:00',
	custom_time_start: '',
  custom_time_end: '',
};
export default {
	watch: {
	  value(newValue) {
	    this.val = newValue;
    },
	  val(newVal) {
	    this.$emit('input', newVal);
    },
		data(newData) {
			this.response = null;
			this.errors = {};
			if (newData && newData.id) {
				this.isEditing = true;
				this.setData(newData);
			} else {
				this.isEditing = false;
				this.setData(defaultPlaylistData);
			}
		},
	},
	props: {
	  value: {
	    type: Boolean,
      required: true
    },
		data: {
			required: false,
		},
		channel: {
			type: [Object, Array],
			required: true,
		}
	},
	mounted() {
		if (this.data) {
			this.setData(this.data);
		}
		this.playbackWeightOptions = [];
		for (let i = 0; i <= 9; i++) {
			this.playbackWeightOptions.push({
				name: i,
				value: i
			});
		}
		let playbackTypeNames = this.$t('dashboard.tracks.playlists.playback_types');
		for (let i = 0; i <= 6; i++) {
			this.playbackTypeOptions.push({
				name: playbackTypeNames[i],
				value: i
			});
		}
		let playbackOrderNames = this.$t('dashboard.tracks.playlists.playback_orders');
		for (let i = 0; i <= 2; i++) {
			this.playbackOrderOptions.push({
				name: playbackOrderNames[i],
				value: i
			});
		}
		this.initTimeInputs();
	},
	data () {
		return {
		  val: this.value,
			response: null,
			isEditing: this.data && this.data.id,
			loading: false,
			errors: {},
			currentEditingId: null,
			showSelectDatetimeModal: false,
			playbackOrderOptions: [],
			playbackWeightOptions: [],
			playbackTypeOptions: [],
			customTimeValue: '',
			playlist: JSON.parse(JSON.stringify(defaultPlaylistData)),
			additionalData: JSON.parse(JSON.stringify(defaultPlaylistAdditionalData)),
			playTimesDaysActive: {
				1: false,
				2: false,
				3: false,
				4: false,
				5: false,
				6: false,
				7: false,
			},
		}
	},

	methods: {
		setData(data) {
			Object.keys(data).forEach(key=>{
				if (this.playlist[key] !== undefined) {
					this.playlist[key] = data[key];
				}
			});
			if (data.id) {
				this.currentEditingId = data.id;
			};
			if (data.playback_data) {
			  this.additionalData = {...this.additionalData,...data.playback_data};
				if (data.playback_data.play_times) {
				  for (let i = 1; i <= 7; i++) {
				    let dayActive = !!data.playback_data.play_times[i];
				    this.playTimesDaysActive[i] = dayActive;
            if (!dayActive) {
              this.additionalData.play_times[i] = {
                play_from: '12:00',
                play_till: '15:00'
              }
            }
          }
        }
			}
			console.log(this.additionalData);
		},
		addPlaylist() {
			let data = this.playlist;
			let additionalData = {};
			if (data.playback_order === 2) {
			  additionalData.tracks_count = this.additionalData.tracks_count;
      }
			if (data.playback_type === 2 || data.playback_type === 5) {
				additionalData.play_times = this.additionalData.play_times;
        let days = Object.keys(this.playTimesDaysActive).filter(key => this.playTimesDaysActive[key]);
        additionalData.play_times_equal = this.additionalData.play_times_equal;
        additionalData.play_times_equal_from = this.additionalData.play_times_equal_from;
        additionalData.play_times_equal_till = this.additionalData.play_times_equal_till;
				if (this.additionalData.play_times_equal) {
          additionalData.play_times = {};
					if (data.playback_type === 2) {
            days.forEach(day => {
              additionalData.play_times[day] = {
								play_from: this.additionalData.play_times_equal_from,
								play_till: this.additionalData.play_times_equal_till
							};
						});

					} else {
					  days.forEach(day => {
              additionalData.play_times[day] = {
								play_in: this.additionalData.play_times_equal_in,
							};
						});
					}
				} else {
				  let data = {};
				  days.forEach(day => {
				    data[day] = this.additionalData.play_times[day]
          });
          additionalData.play_times = data;
        }
			} else {
				if (data.playback_type === 3) {
					additionalData.minutes_between = parseInt(this.additionalData.minutes_between);
				} else {
					if (data.playback_type === 4) {
						additionalData.tracks_between = parseInt(this.additionalData.tracks_between);
					} else {
						if (data.playback_type === 6) {
							additionalData.custom_time_start = this.additionalData.custom_time_start;
              additionalData.custom_time_end = this.additionalData.custom_time_end;
						}
					}
				}
			}
			console.log(additionalData);
		  data.channel_id = this.channel.id;
			data.playback_data = additionalData;
			this.loading = true;
			if (this.isEditing) {
				this.$axios.put('/radioplaylists/'+this.currentEditingId,data).then(res=>{
					this.loading = false;
					this.response = res.data;
					//this.$refs.panel_box.scrollTop = 0;
					this.errors = res.data.errors || {};
          this.$store.commit('NEW_ALERT',res.data);
					if (res.data.status) {
						this.$emit('editplaylist',res.data.playlist);
						this.val = false;
					}
				})
			} else {
				this.$axios.post('/radioplaylists',data).then(res=>{
					this.loading = false;
					this.response = res.data;
					//this.$refs.panel_box.scrollTop = 0;
					this.errors = res.data.errors || {};
          this.$store.commit('NEW_ALERT',res.data);
					if (res.data.status) {
						this.$emit('newplaylist',res.data.playlist);
            this.val = false;
					}
				});
			}
		},
		hidePanel() {
			this.val = false;
		},
		initTimeInputs() {
			if (this.playlist.playback_type === 2 || this.playlist.playback_type === 5) {
				this.additionalData.play_time = [];
				this.additionalData.play_times_equal = true;
				for (let i = 1; i <= 7; i++) {
					this.playTimesDaysActive[i] = true;
					if (this.playlist.playback_type === 2 ) {
						this.additionalData.play_times[i] = {
							play_from: '12:00',
							play_till: '15:00',
						}
					} else {
						if (this.playlist.playback_type === 5 ) {
							this.additionalData.play_times[i] = {
								play_in: '12:00',
							}
						}
					}
				}
			} else {
				if (this.playlist.playback_type === 6) {

				}
			}
		},
		onPlaybackTypeChange(val) {
			this.initTimeInputs();
		}
	}
}
</script>
