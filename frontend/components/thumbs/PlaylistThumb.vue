<template>
  <common-thumb class="playlist-thumb" :link="link" :picture="data.logo" >
    <template slot="texts">
      <div class="thumb__title">{{data.name}}</div>
      <div class="thumb__small-title">{{data.media_count > 0 ? $tc('playlists.count', data.media_count) : $t('playlists.empty')}}</div>
      <c-statistics-icons class="thumb__statistics-icons" :data="metadata"></c-statistics-icons>
    </template>
    <template slot="list_texts">
      <div class="thumb__description">{{data.description}}</div>

    </template>
  </common-thumb>
</template>
<style lang="scss">
.playlist-thumb {
  .thumb__picture-container {

  }
}
</style>
<script>
  import CommonThumb from "@/components/thumbs/CommonThumb";
  import {formatPublishDate} from "@/helpers/dates";
  export default {
    components: {CommonThumb},
    computed: {
      metadata() {
        return [
          {
            icon: 'fa-clock', value: formatPublishDate(this.data.updated_at), tooltip: this.$t('playlists.updated_at')
          },
        ];
      },
      link() {
        return this.data.local_url;
      },
    },
    data() {
      return {

      }
    },
    props: {
      data: {
        type: [Object],
        required: true
      }
    }
  }
</script>
