<template>
  <c-button flat icon="far-smile" icon-only>
    <c-popup-menu activate-on-parent-click position="top-right">
      <div class="chat__smileys" v-if="config.smileys">
        <div class="chat__smileys__heading" v-if="config.smileys.length && siteSmileys.length">{{$t('chat.smileys.standard')}}</div>
        <div class="chat__smileys__inner" v-if="siteSmileys.length">
          <img @click="$emit('addSmiley', smiley)" :src="smiley.full_url" class="chat__smiley" :key="$index" v-for="(smiley, $index) in siteSmileys"/>
        </div>
        <div class="chat__smileys__heading" v-if="config.smileys.length && siteSmileys.length">{{$t('chat.smileys.custom')}}</div>
        <div class="chat__smileys__inner" v-if="config.smileys.length">
          <img @click="$emit('addSmiley', smiley)" :src="smiley.full_url" class="chat__smiley" :key="$index" v-for="(smiley, $index) in config.smileys"/>
        </div>
      </div>
    </c-popup-menu>
  </c-button>
</template>
<style lang="scss" scoped>
.chat {
  &__smileys {
    position: relative;
    max-width: 20.25em;
    min-height: 3em;
    text-align: left;
    padding: 0 .75em;
    &__heading {
      font-weight: bold;
      margin: .5em 0;
    }
  }
}
</style>
<script>
import {mapGetters} from "vuex";

export default {
  computed: {
    ...mapGetters('config', ['siteSmileys']),
  },
  props: {
    config: {
      type: Object,
      required: true
    }
  }
}
</script>
