<template>
  <div class="embed" ref="page">
    <div class="embed__player-container" v-if="selectedChannel">
      <RadioPlayer :embed="true" :autoplay="autoplay" :key="selectedChannel.id" :channel="selectedChannel" v-if="selectedChannel.channel_type === 'radio'" />
    </div>
    <div class="embed__channels">
      <div class="list-container">
        <div class="list-container__inner">
          <a @click="setChannel(channel)" class="list-item" :class="{'list-item--selected': selectedChannel && selectedChannel.id === channel.id}" :key="channel.id" v-for="channel in channels">
            <div class="list-item__left">
              <img class="list-item__picture" :src="channel.logo" />
              <div class="list-item__captions">
                <div class="list-item__title">{{channel.name}}</div>
              </div>
            </div>
            <div class="list-item__right">
              <div class="list-item__buttons">
                <c-button target="_blank" :to="'/' + channel.shortname" @click.native="onButtonClick" flat rounded icon-only icon="launch"></c-button>
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .embed {
    background: var(--main-bg);
    height: 100%;
    color: var(--text-color);
    .list-item {
      align-items: center !important;
      flex-direction: row !important;

      &__title {
        margin: 0;
      }

      &__left {
        margin: 0;
      }
    }

    .radio-player__station-info {
      display: none;
    }
  }
</style>
<script>
  import RadioPlayer from '@/components/media-player/RadioPlayer';
  export default {
    layout: 'empty',
    computed: {
        inIframe() {
            try {
                return window.self !== window.top;
            } catch (e) {
                return true;
            }
       }
    },
    methods: {
      setChannel(channel) {
        this.autoplay = true;
        this.selectedChannel = channel;
      },
      onButtonClick(e) {
        console.log(e);
        e.stopPropagation();
      }
    },
    mounted() {
      if (this.channels.length > 0) {
        this.selectedChannel = this.channels[0];
      }
    },
    data() {
      return {
        selectedChannel: null,
        autoplay: false
      }
    },
    components: {
      RadioPlayer
    },
    async asyncData({app, query}) {
      let ids = query.ids.split(",");
      let data = (await app.$api.get(`channels/multiple?ids=${ids}`));
      return {
        channels: data.data.channels
      }
    },
  }
</script>
