<template>
  <div>
    <c-select v-form-input="'action'" :title="$t('dashboard.media.folders.file_actions')" :options="options" />
  </div>
</template>
<script>
export default {
  computed: {
    options() {
      return  [
        {value: 'move_up', name: his.multiple ? this.$t('dashboard.media.folders.delete_options_multiple.move_up') : this.$t('dashboard.media.folders.delete_options.move_up')},
        {value: 'delete', name: this.multiple ? this.$t('dashboard.media.folders.delete_options_multiple.delete') : this.$t('dashboard.media.folders.delete_options.delete')}
      ]
    }
  },
  props: {
    multiple: Boolean,
  }
}
</script>
