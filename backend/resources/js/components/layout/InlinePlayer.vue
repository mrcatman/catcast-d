<template>
<div class="inline-player" :class="{'inline-player--visible':playerVisible, 'inline-player--radio': track && track.is_radio}">
  <nuxt-link :style="{backgroundImage: 'url('+track.channel.logo+')'}" :to="'/'+track.channel.shortname" class="inline-player__radio-logo" v-if="track && track.is_radio && track.channel"></nuxt-link>
	<div class="inline-player__buttons">
		<a class="inline-player__button" @click="playPause()">
			<div class="inline-player__button__element">
				<i v-if="!isPlaying" class="material-icons">play_arrow</i>
				<i v-else class="material-icons">pause</i>
			</div>
		</a>
	</div>
	<div class="inline-player__info">
		<audio controls style="display:none" ref="player"></audio>
    <div class="inline-player__texts" v-if="track">
      <div class="inline-player__title">{{track.title}}</div>
			<div class="inline-player__author" v-if="track.author">{{track.author}}</div>
      <div class="inline-player__author" v-else-if="track.artist">{{track.artist}}</div>
		</div>
		<div class="inline-player__progress" v-if="!track || !track.is_radio" @click="onProgressClick" ref="progress">
			<div class="inline-player__progress__inner">
				<div class="inline-player__progress__track"  ref="track" :style="{left:(trackState.progress*100)+'%'}"></div>
				<div class="inline-player__progress__bar" ref="bar" :style="{width:(trackState.progress*100)+'%'}"></div>
			</div>
		</div>
	</div>
	<div class="inline-player__time" v-if="track && !track.is_radio">
		{{getFormattedTime(trackState.time)}} / {{getFormattedTime(trackState.length)}}
	</div>
  <div class="inline-player__buttons">
    <a class="inline-player__button" @click="hidePlayer()">
      <div class="inline-player__button__element">
        <i class="material-icons">stop</i>
      </div>
    </a>
  </div>
</div>
</template>
<style lang="scss">
.inline-player {
    position: fixed;
    z-index: 1;
    left: calc(50% - 18em);
    width: 36em;
    background: var(--box-footer-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    bottom: -5em;
    transition: all .4s;
    color: var(--text-color);
    box-shadow: 0 0 1.75em -.5em var(--active-color);
    &--visible {
      z-index: 100000000;
      bottom: 0;
    }
    &__radio-logo {
      width: 3.25em;
      height: 3.25em;
      background-repeat: no-repeat;
      background-position: center center;
      background-size: cover;
      margin: 0 .5em;
    }
    &__info{
      max-width: calc(100% - 14.5em);
      margin: 0 1em 0 0;
      flex: 1;
      display:flex;
      flex-direction:column;
    }
    &--radio &__info {
      max-width: calc(100% - 8.5em);
    }
    &__buttons {
      display: flex;
      height:100%;
    }
    &__button {
      cursor: pointer;
      &__element {
        line-height: 0;
        padding: 1em;
        display: inline-flex;
      }
    }
    &__time{
      white-space: nowrap;
      padding: 0 .5em;
    }
    &__texts {
      flex:1;
      display:flex;
      flex-direction:column;
      justify-content:center;
      padding: .5em 0 0;
      white-space: nowrap;
    }

    &__title {
      overflow: hidden;
      text-overflow: ellipsis;
      font-weight: 400;
      font-size: 1em;
    }

    &__author {
      overflow: hidden;
      text-overflow: ellipsis;
      font-size: 1.25em;
    }
	&__progress {
		position: relative;
		height: 3px;
		padding:.5em 0;
		width: 100%;
		cursor:pointer;
		&__inner{
			background: rgba(255, 255, 255, 0.35);
			width: 100%;
			height: 3px;
		}
		&__bar {
			background: var(--active-color);
			height: 3px;
		}
	}
}

</style>
<script>
import {mapState} from 'vuex';
const trackUpdateInterval = 10000;
export default {
	computed: {
		...mapState({
      needLoad: state => {
        return state.player.needLoad;
      },
      playerElement: state => {
        return state.player.element;
      },
			playerVisible: state => {
				return state.player.visible;
			},
			track: state => {
				return state.player.track;
			}
		})
	},
	data(){
		return {
      getTrackInterval: null,
      defaultPlayer: true,
			isPlaying:false,
      player: null,
			trackState: {
				progress: 0,
				time: 0,
				length: 0
			}
		}
	},
	mounted() {
	  this.player = document.getElementById('audio');
	  this.player.addEventListener('timeupdate',this.onTimeUpdate);
	},
	watch: {
    playerElement(newElement) {
      //this.defaultPlayer = false;
      //this.player = newElement;
      //this.isPlaying = true;
    },
		isPlaying(playing) {
      if (playing) {
				this.player.play();
			} else {
				this.player.pause();
			}
		},
		track(newTrack, oldTrack) {
      console.log('new track', newTrack);
			if (this.playerVisible) {
			  if (this.needLoad) {
          this.player.src = newTrack.url;
          this.player.addEventListener('timeupdate',this.onTimeUpdate);
        }
        if (!newTrack.is_radio || !oldTrack || (newTrack.channel && oldTrack.channel && newTrack.channel.id !== oldTrack.channel.id)) {
          this.isPlaying = true;
        }
			  if (oldTrack && newTrack.url !== oldTrack.url) {
			    console.log('new track');
          this.player.pause();
          this.player.currentTime = 0;
          this.player.play();
        }
        if (newTrack.is_radio) {
			    if (!oldTrack || newTrack.channel.id !== oldTrack.channel.id) {
            clearInterval(this.getTrackInterval);
            this.getTrackInterval = setInterval(() => {
              this.getCurrentTrack();
            }, trackUpdateInterval);
          }
        } else {
          if (this.getTrackInterval) {
            clearInterval(this.getTrackInterval);
          }
        }
			}
		}
	},
	methods: {
    async getCurrentTrack() {
      if (this.track && this.track.channel) {
        let res = (await this.$api.get(`/radiostream/getcurrenttrack/${this.track.channel.id}`)).data;
        let track = this.track;
        track.title = res.track.title;
        track.artist = res.track.artist;
        this.$store.commit('SET_PLAYER_TRACK_WITHOUT_LOAD', {track});
      }
    },
	  hidePlayer() {
	    this.isPlaying = false;
	    this.$store.commit("HIDE_PLAYER");
    },
		getFormattedTime(time) {
      if (!time) {
        return "";
      }
			let text = "";
			time = Math.ceil(time);
			let hours,minutes,seconds = null;
			if (time >= 3600) {
				hours = Math.floor(time / 3600);
				text = hours+":";
				time = time - hours*3600;
			}
			if (time >= 60) {
				minutes = Math.floor(time / 60);
				if (minutes < 10 && hours>0) {
					minutes = "0"+minutes;
				}
				text = text+minutes+":";
				time = time - minutes*60;
			} else {
				text = text+"0:";
			}
			seconds = time;
			if (seconds < 10) {
				seconds = "0"+seconds;
			}
			text = text+seconds;
			return text;
		},
		onProgressClick(e) {
			let x = e.offsetX;
			let width = e.target.clientWidth;
			let progress = x/width;
			let time = this.trackState.length * progress;
			this.player.currentTime = time;
		},
		playPause() {
			this.isPlaying = !this.isPlaying;
		},
		onTimeUpdate(e) {
			this.trackState.time = e.target.currentTime;
			this.trackState.length = e.target.duration;
			this.trackState.progress = ((e.target.duration && e.target.duration>0) ? (e.target.currentTime / e.target.duration) : 0);
		}
	}
}
</script>
