<template>
  <div class="timetable-page" ref="page">
    <Timetable :channel="channel" v-if="!error" />
    <c-error-page v-else :data="error"/>
  </div>
</template>
<script>
  import Timetable from '@/components/Timetable';
  export default {
    layout: 'empty',
    components: {
      Timetable,
    },
    async asyncData({app,params}) {
      let channelData = (await app.$api.get(`/channels/${params.id}`));
      if (channelData.status) {
        let channel = channelData.data;
        let id = params.id;
        return {
          error: null,
          channel,
          id
        };
      } else {
        return {
          error: channelData
        };
      }
    },
    mounted() {
    }
  }
</script>
