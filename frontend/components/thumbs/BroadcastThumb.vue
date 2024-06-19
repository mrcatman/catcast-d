<template>
  <c-list-item class="broadcast" :highlighted="!data.started_at && dashboard" @click="editBroadcast()" :to="this.data.ended_at ? `/dashboard/${data.channel_id}/broadcasts/${data.id}` : undefined">
    <template slot="captions">
      <div class="list-item__title">
        {{data.title}}
      </div>
      <c-statistics-icons :data="metadata" />
      <div class="list-item__under-title list-item__under-title--small" v-if="description.length" v-html="description"></div>
      <c-tag v-if="data.tags" v-for="tag in data.tags">{{tag}}</c-tag>
      <div v-if="data.media && data.media.length" class="broadcast__media-list">
        <div v-for="media in data.media"  class="broadcast__media-list__item">
          <c-tooltip position="center-right">
            {{media.title}}
          </c-tooltip>
          <video-thumb :data="media" :config="getMediaItemConfig(media)" />
        </div>

      </div>

    </template>
    <template slot="buttons">
      <div class="buttons-row" v-if="dashboard">
        <c-button @click="deleteBroadcast(data)" v-if="data.can_delete" color="red">{{$t('global.delete')}}</c-button>
      </div>
    </template>
  </c-list-item>
</template>
<style lang="scss" scoped>
.broadcast {
  &__media-list {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -.5em;
  }
}
</style>
<script>
import { formatFullDate, formatPublishDate } from "@/helpers/dates";
import BroadcastMetadataEditor from "@/components/dashboard/broadcast/BroadcastMetadataEditor.vue";
import VideoThumb from "@/components/thumbs/VideoThumb.vue";
import {parseMarkdown} from "@/helpers/markdown";

export default {
  components: {VideoThumb},
  computed: {
    description() {
      return parseMarkdown(this.data.description || '');
    },
    metadata() {
      const metadata = [
        this.data.ended_at ? {
          icon: 'fa-clock',
          value: formatPublishDate(this.data.started_at) === formatPublishDate(this.data.ended_at) ? formatPublishDate(this.data.started_at) : `${formatPublishDate(this.data.started_at)} - ${formatPublishDate(this.data.ended_at)}`,
          tooltip: `${formatFullDate(this.data.started_at)} - ${formatFullDate(this.data.ended_at)}`,
        } : {
          icon: 'fa-calendar',
          value: `${formatPublishDate(this.data.will_start_at)}`,
          tooltip: `${formatFullDate(this.data.will_start_at)} - ${formatFullDate(this.data.will_end_at)}`,
        }
      ];
      if (this.data.views) {
        metadata.push({
          icon: 'remove_red_eye',
          value: this.data.views
        })
      }
      if (this.data.category) {
        metadata.push({
          icon: 'fa-gamepad',
          value: this.data.category.name
        })
      }
      return metadata;
    },
  },
  methods: {
    getMediaItemConfig(media) {
      return {
        link: `/dashboard/${media.channel_id}/media/${media.uuid}`,
        showParts: {
          texts: false
        }
      }
    },
    editBroadcast() {
      if (this.data.can_edit && !this.data.ended_at) {
        this.$store.commit('modals/showStandardModal', {
          confirm: true,
          component: BroadcastMetadataEditor,
          buttonColor: '',
          buttonText: this.$t('global.save'),
          title: this.$t('dashboard.broadcast.create'),
          props: {planned: true},
          formValues: {
            ...this.data,
          },
          fn: async (broadcast) => {
            await this.$api.put(`broadcasts/${this.data.id}`, broadcast);
            this.$emit('reload');
          },
        })
      }
    },
    deleteBroadcast() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        title: this.$t('dashboard.broadcast.delete'),
        fn: async () => {
          await this.$api.delete(`broadcasts/${this.data.id}`);
          this.$emit('reload');
        },
      })
    },
  },
  props: {
    data: {
      type: Object,
      required: true
    },
    dashboard: Boolean
  }
}
</script>
