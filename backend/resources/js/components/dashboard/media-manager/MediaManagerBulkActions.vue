<template>
  <div v-show="showButtons" class="media-manager__bulk-actions">
    <div class="buttons-row">
      <c-button v-if="config.enableTransfer" @click="bulkTransfer()" icon="fa-plus">{{$t('global.add')}}</c-button>
      <c-button v-if="!config.disableEdit" @click="bulkMove()" icon="fa-folder">{{$t('dashboard.media.actions.move_to_folder')}}</c-button>
      <c-button v-if="!config.disableEdit" @click="bulkDelete()" icon="close" color="red">{{$t('dashboard.media.actions.delete')}}</c-button>
      <c-button @click="unselect()" flat>{{$t('global.cancel')}}</c-button>
    </div>
  </div>
</template>
<script>
import MediaManagerFolderSelect from '@/components/dashboard/media-manager/MediaManagerFolderSelect';
import MediaManagerFolderDeleteActions from '@/components/dashboard/media-manager/MediaManagerFolderDeleteActions';

export default {
  computed: {
    selectedItemIdsAsArray() {
      return Object.keys(this.selectedItemIds).filter(id => !!this.selectedItemIds[id]);
    },
    selectedMediaIds() {
      return this.selectedItemIdsAsArray.filter(id => id > 0);
    },
    selectedFolderIds() {
      return this.selectedItemIdsAsArray.filter(id => id < 0).map(id => id * -1);
    },
    showButtons() {
      return Object.values(this.selectedItemIdsAsArray).filter(value => !!value).length > 0;
    },
    exceptFolderIds() {
      return [
        this.folderId ? this.folderId : -1,
        ...this.selectedFolderIds
      ];
    }
  },
  methods: {
    unselect() {
      this.$emit('unselect');
    },
    bulkTransfer() {
      this.$emit('transfer');
    },
    bulkMove() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        title: this.selectedFolderIds.length > 0 ? this.$t('dashboard.media.bulk_move_folders') : this.$t('dashboard.media.bulk_move'),
        text: '',
        buttonColor: '',
        buttonText: this.$t('global.move'),
        component: MediaManagerFolderSelect,
        data: {channel_id: this.channel.id, except: this.exceptFolderIds},
        formValues: {folder_id: -1},
        fn: async ({folder_id}) => {
          await this.$api.post(`/channels/${this.channel.id}/media/bulk-move`, {
            move_to: folder_id,
            media_ids: this.selectedMediaIds,
            folder_ids: this.selectedFolderIds,
          });
          this.$emit('update');
        },
      })
    },
    bulkDelete() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        title: '',
        text: this.selectedFolderIds.length > 0 ? this.$t('dashboard.media.confirm_bulk_delete_folders') : this.$t('dashboard.media.confirm_bulk_delete'),
        component: this.selectedFolderIds.length > 0 ? MediaManagerFolderDeleteActions : null,
        props: {multiple: this.selectedFolderIds.length > 1},
        data: {action: 'move_up'},
        fn: async ({action}) => {
          await this.$api.post(`/channels/${this.channel.id}/media/bulk-delete`, {
            action,
            media_ids: this.selectedMediaIds,
            folder_ids: this.selectedFolderIds,
          });
          this.$emit('update');
        },
      })
    }
  },
  props: {
    channel: {
      type: Object,
      required: true
    },
    selectedItemIds: {
      type: Object,
      required: true
    },
    folderId: Number,
    config: {
      type: Object,
      required: false,
      default() {
        return {}
      }
    },
  }
}
</script>
