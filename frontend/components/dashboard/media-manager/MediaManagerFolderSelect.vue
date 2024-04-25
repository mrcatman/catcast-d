<template>
  <div>
    <c-preloader v-if="loading" />
    <c-select v-else v-form-input="'folder_id'" :title="$t('dashboard.media.select_folder_to_move')" :options="options" />
  </div>
</template>
<script>
export default {
  computed: {
    options() {
      return [
        {value: -1, name: this.$t('dashboard.media.main_folder')},
        ...this.folders.map(folder => {
          return {
            value: folder.id,
            name: folder.title
          }
        })
      ].filter(item => {
        return this.except.indexOf(item.value) === -1;
      })
    }
  },
  async mounted() {
    this.folders = await this.$api.get(`/channels/${this.channelId}/media/folders`, {onError: []});
    this.loading = false;
  },
  data() {
    return {
      loading: true,
      folders: [],
    }
  },
  props: {
    channelId: Number,
    except: Array,
  }
}
</script>
