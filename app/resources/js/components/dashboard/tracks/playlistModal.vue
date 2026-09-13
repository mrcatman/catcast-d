<template>
<div class="modal-panel">
  <div class="modal-panel__inner">
    <div class="modal-panel__background" @click="hidePanel()"></div>
    <div class="modal-panel__box" >
      <div class="box box--flex-vertical box--with-header">
        <div class="box__header">
          <span v-if="isEditing" class="box__header__title">{{$t('dashboard.tracks.playlists.edit_playlist')}}</span>
          <span v-else class="box__header__title">{{$t('dashboard.tracks.playlists.add_playlist')}}</span>
        </div>
        <div class="box__inner" ref="panel_box">
          <div class="modal-panel__inputs">
            <c-response :data="response" />
            <div class="dashboard__input-container">
              <c-input :errors="errors.title" :title="$t('dashboard.tracks.playlists.title')" v-model="playlist.title" />
            </div>
            <div class="dashboard__input-container">
              <c-input type="textarea" :errors="errors.description" :title="$t('dashboard.tracks.playlists.description')" v-model="playlist.description" />
            </div>
            <div class="dashboard__input-container dashboard__input-container--inline">
              <div class="dashboard__input-container--inline__title">{{$t('dashboard.tracks.playlists.playback_weight')}}</div>
              <div class="dashboard__input-container--inline__element">
                <c-select :errors="errors.playback_weight" placeholder="" :options="playbackWeightOptions" v-model="playlist.playback_weight" />
              </div>
            </div>
            <div class="dashboard__input-container dashboard__input-container--inline">
              <div class="dashboard__input-container--inline__title">{{$t('dashboard.tracks.playlists.playback_type')}}</div>
              <div class="dashboard__input-container--inline__element">
                <c-select @change="onPlaybackTypeChange" :errors="errors.playback_type" placeholder="" :options="playbackTypeOptions" v-model="playlist.playback_type" />
              </div>
            </div>
            <div v-if="playlist.playback_type === 2 || playlist.playback_type === 5">
              <div class="dashboard__input-container">
                <c-checkbox switch :title="$t('dashboard.tracks.playlists.play_times_equal')" v-model="additionalData.play_times_equal" />
              </div>
              <div class="vertical-delimiter"></div>
              <div class="row row--centered">
                <div class="col">
                  <div class="row" :key="i" v-for="i in 7">
                    <div class="col col--auto" >
                      <c-checkbox v-if="playTimesDaysActive[i] !== undefined" v-model="playTimesDaysActive[i]" :title="$t('weekdays_short')[i]"  />
                    </div>
                    <div class="col" v-if="!additionalData.play_times_equal">
                      <div v-if="playlist.playback_type === 2" style="justify-content:flex-end" class="dashboard-page__row-with-inputs" v-show="playTimesDaysActive[i]">
                        <span class="dashboard-page__row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_from')}}</span>
                        <div class="dashboard-page__row-with-inputs__element">
                          <c-time-picker :errors="errors['playback_data.play_times.'+i+'.play_from']" v-model="additionalData.play_times[i].play_from" />
                        </div>
                        <span class="dashboard-page__row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_till')}}</span>
                        <div class="dashboard-page__row-with-inputs__element">
                          <c-time-picker :errors="errors['playback_data.play_times.'+i+'.play_till']" v-model="additionalData.play_times[i].play_till" />
                        </div>
                      </div>
                      <div v-else-if="playlist.playback_type === 5" style="justify-content:flex-end" class="dashboard-page__row-with-inputs" v-show="playTimesDaysActive[i]">
                        <span class="dashboard-page__row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_in')}}</span>
                        <div class="dashboard-page__row-with-inputs__element">
                          <c-time-picker :errors="errors['playback_data.play_times.'+i+'.play_in']" v-model="additionalData.play_times[i].play_in" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col" v-if="additionalData.play_times_equal">
                  <div v-if="playlist.playback_type === 2"  class="dashboard-page__row-with-inputs">
                    <span class="dashboard-page__row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_from')}}</span>
                    <div class="dashboard-page__row-with-inputs__element">
                      <c-time-picker :errors="errors['playback_data.play_times.0.play_from']"  v-model="additionalData.play_times_equal_from" />
                    </div>
                    <span class="dashboard-page__row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_till')}}</span>
                    <div class="dashboard-page__row-with-inputs__element">
                      <c-time-picker :errors="errors['playback_data.play_times.0.play_till']" v-model="additionalData.play_times_equal_till" />
                    </div>
                  </div>
                  <div v-else-if="playlist.playback_type === 5"  class="dashboard-page__row-with-inputs">
                    <span class="dashboard-page__row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_in')}}</span>
                    <div class="dashboard-page__row-with-inputs__element">
                      <c-time-picker :errors="errors['playback_data.play_times.0.play_to']" v-model="additionalData.play_times_equal_in" />
                    </div>
                  </div>
                </div>
              </div>
              <div class="vertical-delimiter"></div>
            </div>
            <div v-else-if="playlist.playback_type === 3"  class="dashboard-page__row-with-inputs">
              <span class="dashboard-page__row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_every')}}</span>
              <div style="flex:1" class="dashboard-page__row-with-inputs__element">
                <c-input :errors="errors['playback_data.minutes_between']" :min="10" :max="3600" type="number" v-model="additionalData.minutes_between" />
              </div>
              <span class="dashboard-page__row-with-inputs__text">{{$t('dashboard.tracks.playlists.play_every_minutes')}}</span>
            </div>
            <div v-else-if="playlist.playback_type === 4" class="dashboard__input-container dashboard__input-container--inline">
              <div class="dashboard__input-container--inline__title">{{$t('dashboard.tracks.playlists.tracks_between')}}</div>
              <div class="dashboard__input-container--inline__element">
                <c-input :errors="errors['playback_data.tracks_between']" type="number" :min="1" :max="1000"  placeholder="" v-model="additionalData.tracks_between" />
              </div>
            </div>
            <div v-if="playlist.playback_type === 6"  class="dashboard__input-container dashboard__input-container--inline">
              <div class="dashboard__input-container--inline__title">{{$t('dashboard.tracks.playlists.custom_time_start')}}</div>
              <div class="dashboard__input-container--inline__element">
                <c-datetime-picker :dialogTitle="$t('dashboard.tracks.playlists.custom_time_start')" :errors="errors['playback_data.custom_time_start']" title="" v-model="additionalData.custom_time_start" />
              </div>
            </div>
            <div v-if="playlist.playback_type === 6"  class="dashboard__input-container dashboard__input-container--inline">
              <div class="dashboard__input-container--inline__title">{{$t('dashboard.tracks.playlists.custom_time_end')}}</div>
              <div class="dashboard__input-container--inline__element">
                <c-datetime-picker :dialogTitle="$t('dashboard.tracks.playlists.custom_time_end')" :errors="errors['playback_data.custom_time_end']" title="" v-model="additionalData.custom_time_end" />
              </div>
            </div>
            <div class="dashboard__input-container dashboard__input-container--inline">
              <div class="dashboard__input-container--inline__title">{{$t('dashboard.tracks.playlists.playback_order')}}</div>
              <div class="dashboard__input-container--inline__element">
                <c-select :errors="errors.playback_order" placeholder="" :options="playbackOrderOptions" v-model="playlist.playback_order" />
              </div>
            </div>
            <div class="dashboard__input-container dashboard__input-container--inline" v-if="playlist.playback_order == 2">
              <div class="dashboard__input-container--inline__title">{{$t('dashboard.tracks.playlists.tracks_count')}}</div>
              <div class="dashboard__input-container--inline__element">
                <c-input :errors="errors['playback_data.tracks_count']" type="number" :min="1" :max="50"  v-model="additionalData.tracks_count" />
              </div>
            </div>
            <div class="row row--centered">
              <div class="col">
                <div class="dashboard__input-container">
                  <c-checkbox :errors="errors.is_visible" :description="$t('dashboard.tracks.playlists.is_visible_description')" :title="$t('dashboard.tracks.playlists.is_visible')" v-model="playlist.is_visible" />
                </div>
              </div>
              <div class="col col--auto" v-show="playlist.is_visible">
                <c-picture-uploader :title="$t('dashboard.tracks.playlists.cover')" :channelid="channel.id" folder="playlist_covers" v-model="playlist.cover" />
              </div>
            </div>
          </div>
        </div>
        <div class="box__footer">
          <div class="buttons-row">
            <c-button v-if="!isEditing" :loading="loading" @click="addPlaylist()">{{$t('dashboard.tracks.playlists.add_playlist_button')}}</c-button>
            <c-button v-else :loading="loading" @click="addPlaylist()">{{$t('dashboard.tracks.playlists.edit_playlist_button')}}</c-button>
            <c-button color="red" @click="hidePanel()">{{$t('global.cancel')}}</c-button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
</template>
<style lang="scss">

</style>
<script>


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
	watch: {
		data(newData) {
			this.response = null;
			this.errors = {};
			console.log('new data', newData);
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
		data: {
			required:false,
		},
		channel: {
			type:[Object,Array],
			required:true,
		}
	},
	mounted() {
		if (this.data) {
			this.setData(this.data);
		}
		this.playbackWeightOptions = [];
		for (let i=0;i<=9;i++) {
			this.playbackWeightOptions.push({
				name:i,
				value:i
			});
		}
		let playbackTypeNames = this.$t('dashboard.tracks.playlists.playback_types');
		for (let i=0;i<=6;i++) {
			this.playbackTypeOptions.push({
				name:playbackTypeNames[i],
				value:i
			});
		}
		let playbackOrderNames = this.$t('dashboard.tracks.playlists.playback_orders');
		for (let i = 0; i<=2; i++) {
			this.playbackOrderOptions.push({
				name: playbackOrderNames[i],
				value: i
			});
		}
		this.initTimeInputs();
	},
	data () {
		return {
			response:null,
			isEditing:false,
			loading:false,
			errors: {},
			currentEditingId:null,
			showSelectDatetimeModal:false,
			playbackOrderOptions:[],
			playbackWeightOptions:[],
			playbackTypeOptions:[],
			customTimeValue: '',
			playlist:JSON.parse(JSON.stringify(defaultPlaylistData)),
			additionalData:JSON.parse(JSON.stringify(defaultPlaylistAdditionalData)),
			playTimesDaysActive: {
				1:false,
				2:false,
				3:false,
				4:false,
				5:false,
				6:false,
				7:false,
			},
		}
	},

	methods: {
		setData(data) {
			Object.keys(data).forEach(key=>{
				if (this.playlist[key]!==undefined) {
					this.playlist[key] = data[key];
				}
			});
			if (data.id) {
				this.currentEditingId = data.id;
			};
			if (data.playback_data) {
				this.additionalData = {...this.additionalData,...data.playback_data};
			}
		},
		addPlaylist() {
			let data = this.playlist;
			let additionalData = {};
			if (data.playback_order === 2) {
			  additionalData.tracks_count = this.additionalData.tracks_count;
      }
			if (data.playback_type === 2 || data.playback_type === 5) {
				additionalData.play_times = this.additionalData.play_times;
				if (this.additionalData.play_times_equal) {
					let days = Object.keys(this.playTimesDaysActive).filter(key => this.playTimesDaysActive[key]);
					if (data.playback_type === 2) {
						additionalData.play_times = days.map(day=>{
							return {
								play_from:this.additionalData.play_times_equal_from,
								play_till:this.additionalData.play_times_equal_till
							};
						});

					} else {
						additionalData.play_times = days.map(day=>{
							return {
								play_in:this.additionalData.play_times_equal_in,
							};
						});
					}
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
			data.channel_id = this.channel.id;
			data.playback_data = additionalData;
			this.loading = true;
			if (this.isEditing) {
				this.$axios.put('/radioplaylists/'+this.currentEditingId,data).then(res=>{
					this.loading = false;
					this.response = res.data;
					this.$refs.panel_box.scrollTop = 0;
					this.errors = res.data.errors || {};
					if (res.data.status) {
						this.$store.commit('NEW_ALERT',res.data);
						this.$emit('editplaylist',res.data.playlist);
						this.$emit('close');
					}
				})
			} else {
				this.$axios.post('/radioplaylists',data).then(res=>{
					this.loading = false;
					this.response = res.data;
					this.$refs.panel_box.scrollTop = 0;
					this.errors = res.data.errors || {};
					if (res.data.status) {
						this.$store.commit('NEW_ALERT',res.data);
						this.$emit('newplaylist',res.data.playlist);
						this.$emit('close');
					}
				});
			}
		},
		hidePanel() {
			this.$emit('close');
		},
		initTimeInputs() {
			if (this.playlist.playback_type === 2 || this.playlist.playback_type === 5) {
				this.additionalData.play_time = [];
				this.additionalData.play_times_equal = true;
				for (let i=1;i<=7;i++) {
					this.playTimesDaysActive[i] = true;
					if (this.playlist.playback_type === 2 ) {
						this.additionalData.play_times[i] = {
							play_from:'12:00',
							play_till:'15:00',
						}
					} else {
						if (this.playlist.playback_type === 5 ) {
							this.additionalData.play_times[i] = {
								play_in:'12:00',
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
