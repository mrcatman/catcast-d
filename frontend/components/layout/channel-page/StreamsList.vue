<template>
  <m-box class="box--in-list streams-list" :no-padding="true">
    <template v-slot:heading>{{$t('streams.list')}}</template>
    <template v-slot:default>
      <m-list class="streams-list">
        <m-list-item v-for="stream in streams.list" :key="stream.id">
          <m-list-item-texts>
            <m-list-item-title>
              {{stream.name}}
            </m-list-item-title>
            <m-list-item-sub>
              {{getStreamTime(stream)}}
            </m-list-item-sub>
          </m-list-item-texts>
        </m-list-item>
        <m-list-bottom v-if="streams.pagesCount > 1">
          <m-pager v-model="currentPage" :data="streams" />
        </m-list-bottom>

      </m-list>
    </template>
  </m-box>
</template>
<style lang="scss">

</style>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import Channel from '~/types/Channel'
import { Prop, Watch } from '~/node_modules/vue-property-decorator'
import { ChannelGetStreams } from '~/api/modules/channels'

@Component({
  components: { },
})
export default class ChannelInfoBlock extends Vue {
  @Prop({required: true}) readonly channel!: Channel
  streams = {};

  public currentPage: number = 1;
  @Watch('currentPage')
  onCurrentPageChange() {
    this.load();
  }

  getStreamTime(stream) {
    let startDate = new Date(stream.started_at);
    let startDateString = startDate.toLocaleDateString();
    let startTimeString = startDate.toLocaleTimeString().replace(/(.*)\D\d+/, '$1');
    let start = startDateString + ' ' + startTimeString;
    if (stream.ended_at) {
      let endDate = new Date(stream.ended_at);
      let endDateString =  endDate.toLocaleDateString();
      let endTimeString = endDate.toLocaleTimeString().replace(/(.*)\D\d+/, '$1');
      let end = startDateString === endDateString ? endTimeString : (endDateString + ' ' + endTimeString);
      if (startDateString !== endDateString || startTimeString !== endTimeString) {
        return `${start} - ${end}`;
      }

    }
    return start;
  }

  async load() {
    this.streams = await ChannelGetStreams(this.channel.id, this.currentPage);
  }
  async mounted() {
   await this.load();
  }
}
</script>
<style lang="scss">
.streams-list {
  margin-bottom: 1em;
}
</style>
