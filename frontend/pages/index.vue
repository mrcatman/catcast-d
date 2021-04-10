<template>
  <div class="container index-page">
    <div class="index-page__about">
      <div class="index-page__about__text" v-html="config.site_description"></div>
    </div>
    <ChannelsList :title="$t('channels.lists.online')" link="/channels/online" :fn="ChannelsGetOnline" />
    <ChannelsList :title="$t('channels.lists.local')" link="/channels/local" :fn="ChannelsGetLocal" />
    <ChannelsList :title="$t('channels.lists.all')" link="/channels/all" :fn="ChannelsGet" />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'

import { ChannelsGet, ChannelsGetLocal, ChannelsGetOnline } from '~/api/modules/channels'
import ChannelsList from '~/components/layout/lists/ChannelsList.vue'

@Component({
  components: {
    ChannelsList,
  },
})
export default class IndexPage extends Vue {

  get config() {
    return this.$accessor.modules.site.config;
  }

  ChannelsGet = ChannelsGet;
  ChannelsGetLocal = ChannelsGetLocal;
  ChannelsGetOnline = ChannelsGetOnline;
}
</script>

<style lang="scss">
.index-page {
  &__about {
    padding: 1em;
    background: var(--box-color);
    margin: 1em 0 0;
    font-size: 1.125em;
    box-shadow: .5em .5em 1.75em var(--active-color);

    &__text {

    }
  }
}
</style>
