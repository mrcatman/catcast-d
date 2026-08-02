<template>
  <div>
    <div class="media-player__embed-code">{{ embedCode }}</div>
    <c-row>
      <c-col>
        <c-input type="number" :title="$t('player.embed.inputs.width')" v-model="data.width" :min="1" :max="1920"/>
      </c-col>
      <c-col>
        <c-input type="number" :title="$t('player.embed.inputs.height')" v-model="data.height" :min="1" :max="1080"/>
      </c-col>
    </c-row>
    <c-checkbox switch :title="$t('player.embed.inputs.autoplay')" v-model="data.autoplay" v-if="!media" />
  </div>
</template>
<style lang="scss">
.media-player {
  &__embed-code {
    max-width: 37em;
    word-break: break-word;
    padding: .5em;
    background: var(--lighten-1);
  }
}
</style>
<script>
import {mapGetters} from "vuex";

export default {
  data() {
    return {
      data: {
        autoplay: true,
        width: 640,
        height: 360,
      }
    }
  },
  computed: {
    ...mapGetters('config', ['siteURL']),
    embedCode() {
      const url = !this.media ? `player/${this.channel.id}` : `media/player/${this.media.uuid}`;
      const autoplay = this.data.autoplay || this.media ? '' : '?autoplay=0';
      return `<iframe src="${this.siteURL}/${url}${autoplay}" frameborder="0" allowfullscreen width="${this.data.width}" height="${this.data.height}"></iframe>`;
    },
  },
  props: {
    media: {
      type: Object,
      required: false
    },
    channel: {
      type: Object,
      required: true
    },
  }
}
</script>
