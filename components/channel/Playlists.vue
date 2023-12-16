<template>
  <div class="channel-layout__playlists">
    <c-thumbs-list :config="config">
      <template slot="item" slot-scope="props">
        <playlist-thumb :data="props.item" />
      </template>
    </c-thumbs-list>
  </div>
</template>
<script>
  import PlaylistThumb from "@/components/thumbs/PlaylistThumb";
  export default {
    computed: {
      config() {
        return {
          url: `/channels/${this.channel.id}/playlists`,
          search: true,
          canChangeView: true
        }
      }
    },
    components: {
      PlaylistThumb,
    },
    props: {
      channel: {
        type: Object,
        required: true,
      }
    },
  }
</script>
