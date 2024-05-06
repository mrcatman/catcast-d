<template>
  <c-list-item>
    <template slot="captions">
      <div class="list-item__title">
        {{data.title}}
      </div>
      <c-statistics-icons :data="getBroadcastInfo" />
      <div class="list-item__under-title list-item__under-title--short" v-if="data.description && data.description.length">
        <div v-html="data.display_description"></div>
      </div>
      <c-tag v-if="data.tags" v-for="tag in data.tags">{{tag}}</c-tag>
    </template>
    <template slot="buttons">
      <div class="buttons-row" v-if="showEditButtons">
        <c-button @click="editBroadcast(data)" v-if="data.can_edit" color="green">{{$t('global.edit')}}</c-button>
        <c-button @click="deleteBroadcast(data)" v-if="data.can_delete" color="red">{{$t('global.delete')}}</c-button>
      </div>
    </template>
  </c-list-item>
</template>
<script>
import { formatFullDate, formatPublishDate } from "@/helpers/dates";
import BroadcastMetadataEditor from "@/components/dashboard/broadcast/BroadcastMetadataEditor.vue";

export default {
  computed: {
    getBroadcastInfo() {
      const info = [
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
      if (this.data.category) {
        info.push({
          icon: 'fa-gamepad',
          value: this.data.category.name
        })
      }
      return info;
    },
  },
  methods: {
    editBroadcast() {
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
    showEditButtons: Boolean
  }
}
</script>
