<template>
  <div id="video_players" class="detached-players">
    <media-player :detached="!!player.detached" :id="'player_' + $id" :key="$id" :channel="player.channel" :video="player.video" :project="player.project" :page="player.page" v-for="(player, $id) in players.list"  /> <!--  @changeprogram="onChangeProgram" -->
  </div>
</template>
<style lang="scss">
  .detached-players {

  }
</style>
<script>
  import MediaPlayer from "@/components/media-player/MediaPlayer";
  import { mapState } from 'vuex';
  export default{
    computed: {
      ...mapState(['players']),
    },
    components: {
      MediaPlayer
    },
    data() {
      return {
      }
    }
  }
</script>
