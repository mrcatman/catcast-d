<template>
<div class="page-container">
  <c-thumbs-list :config="{...baseConfig, title: $t('channels.online'), buttonUrl: '/tv/online', url: '/channels?type=tv&online=true&order=viewers&count=10'}">
    <template slot="item" slot-scope="props">
      <channel-thumb :data="props.item" />
    </template>
  </c-thumbs-list>
  <c-thumbs-list :config="{...baseConfig, title: $t('channels.popular'), buttonUrl: '/tv/popular', url: '/channels?type=tv&order=page_views&count=10'}">
    <template slot="item" slot-scope="props">
      <channel-thumb :data="props.item" />
    </template>
  </c-thumbs-list>
  <c-thumbs-list :config="{...baseConfig, title: $t('channels.best'), buttonUrl: '/tv/best', url: '/channels?type=tv&order=likes_count&count=10'}">
    <template slot="item" slot-scope="props">
      <channel-thumb :data="props.item" />
    </template>
  </c-thumbs-list>
  <c-thumbs-list :config="{...baseConfig, title: $t('channels.new'), buttonUrl: '/tv/new', url: '/channels?type=tv&order=created_at&count=10'}">
    <template slot="item" slot-scope="props">
      <channel-thumb :data="props.item" />
    </template>
  </c-thumbs-list>
</div>
</template>
<script>
  import ChannelThumb from '@/components/thumbs/ChannelThumb';
  export default {
    head () {
      return {
        title: this.$t('channels.heading'),
      }
    },
    data() {
      return {
        baseConfig: {
          view: 'grid'
        }
      }
    },
    components: {
      ChannelThumb
    },
  }
</script>
