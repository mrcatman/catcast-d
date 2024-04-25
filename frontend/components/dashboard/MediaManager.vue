<template>
  <div class="media-manager" :class="{'media-manager--in-modal': config.inModal}">
    <div class="media-manager__container" @drop="onDrop" @dragover.prevent="onDragStatusChange(true)"
         @dragleave.prevent="onDragStatusChange(false)">
      <div class="media-manager__dragover" v-show="dragging">
        <div class="media-manager__dragover__title">{{ $t('dashboard.media.dragover_title') }}</div>
      </div>


      <c-selection-area @selected="onSelection" :options="{selectables: ['.media-manager__item--selectable']}">
        <c-thumbs-list ref="list" :config="listConfig">
          <template slot="before_heading" v-if="!config.disableUpload">
            <c-box>
              <template slot="title">
                <media-manager-breadcrumbs :breadcrumbs="breadcrumbs" @folderClick="onBreadcrumbsFolderClick"/>
              </template>
              <template slot="title_buttons">
                <media-manager-add-to-upload :channel="channel" :folder-id="currentFolderId" @load="$refs.list.load()"/>
              </template>
            </c-box>
          </template>
          <template slot="filters">
            <c-select class="media-manager__sort" :options="sortOptions" v-model="order"></c-select>
          </template>
          <template slot="before" v-if="currentFolderId">
            <media-manager-item @click="onItemClick" :config="config"
                                 :item="{is_folder: true, object: {id: parentFolderId, channel_id: this.channel.id, title: $t('dashboard.media.back')}}"/>
          </template>
          <template slot="item" slot-scope="props">
            <media-manager-item @click="onItemClick" :config="config" :item="props.item" @edit="onItemEditClick(props.item)"
                                 @delete="onItemDeleteClick(props.item)"
                                 :selected="selectedItemIds[props.item.is_folder ? -1 * props.item.object.id : props.item.object.id]"
                                 @selected="(e) => setItemSelectionState(props.item.is_folder ? -1 * props.item.object.id : props.item.object.id, e)"/>
          </template>
          <template slot="footer" v-if="!config.disableDiskSpaceIndicator || !config.disableEditing">
            <c-box>
              <template slot="main">
                <div class="media-manager__bottom">
                  <media-manager-disk-space  v-if="!config.disableDiskSpaceIndicator" :channel="channel" ref="disk_space"/>
                  <media-manager-bulk-actions v-if="!config.disableEditing" :channel="channel" :folder-id="currentFolderId" :config="config"
                                               :selectedItemIds="selectedItemIds" @update="onBulkUpdate"
                                               @unselect="onBulkUnselect" @transfer="onBulkTransfer"/>
                </div>
              </template>
            </c-box>
          </template>
        </c-thumbs-list>
      </c-selection-area>
    </div>
  </div>
</template>
<script>
import ChangeView from '@/components/ChangeView';
import MediaManagerDiskSpace from '@/components/dashboard/media-manager/MediaManagerDiskSpace';
import MediaManagerItem from '@/components/dashboard/media-manager/MediaManagerItem';
import MediaManagerBreadcrumbs from '@/components/dashboard/media-manager/MediaManagerBreadcrumbs';
import MediaManagerAddToUpload from '@/components/dashboard/media-manager/MediaManagerAddToUpload';
import MediaManagerBulkActions from '@/components/dashboard/media-manager/MediaManagerBulkActions';
import MediaManagerFolderDeleteActions
  from '@/components/dashboard/media-manager/MediaManagerFolderDeleteActions';
import MediaManagerFolderEdit from "@/components/dashboard/media-manager/MediaManagerFolderEdit";
import Team from "@/pages/dashboard/_id/team.vue";

export default {
  components: {
    Team,
    MediaManagerBulkActions,
    ChangeView,
    MediaManagerAddToUpload,
    MediaManagerBreadcrumbs,
    MediaManagerItem,
    MediaManagerDiskSpace,
  },
  async mounted() {
    this.startListeningToEvents();
    await this.loadBreadcrumbs();
    this.$refs.list && await this.$refs.list.load();
  },
  computed: {

    listConfig() {
      let queryParams = {
        order: this.order
      };

      if (this.currentFolderId > 0) {
        queryParams.folder_id = this.currentFolderId;
      }
      return {
        url: `/channels/${this.channel.id}/media/file-manager`,
        queryParams,
        localStorageKey: 'media_manager_view',
        paginate: true,
        infiniteScroll: true,
        canChangeView: true,
        search: true,
        innerScroll: true,
        hideNothingFoundBlock: true,
      }
    },
    sortOptions() {
      return [
        {
          name: this.$t('dashboard.media.sort.date_desc'),
          value: 'date_desc'
        },
        {
          name: this.$t('dashboard.media.sort.date_asc'),
          value: 'date_asc'
        },
        {
          name: this.$t('dashboard.media.sort.views_desc'),
          value: 'views_desc'
        }
      ];
    },
    selectedItems() {
      return this.$refs.list.list.data.filter(item => {
        return !item.is_folder && !!this.selectedItemIds[item.object.id] || item.is_folder && !!this.selectedItemIds[item.object.id * -1] && item.permissions?.can_edit;
      })
    },
    parentFolderId() {
      return this.breadcrumbs.length > 1 ? this.breadcrumbs[this.breadcrumbs.length - 2].id : null;
    },
  },
  watch: {
    $route(route) {
      if (route.params.folder_id) {
        this.goToFolder(parseInt(this.$route.params.folder_id));
      } else {
        this.goToFolder();
      }
    },
    currentFolderId() {
      this.selectedItemIds = {};
      this.search = '';
    },
    view(view) {
      localStorage.media_manager_view = view;
    },

  },
  data() {
    return {
      search: '',
      order: 'date_desc',
      dragging: false,
      currentFolderId: this.$route.params.folder_id ? parseInt(this.$route.params.folder_id) : null,
      breadcrumbs: [],
      view: localStorage.media_manager_view || 'list',
      selectedItemIds: {},
    }
  },
  props: {
    config: {
      type: Object,
      required: false,
      default() {
        return {}
      }
    },
    channel: {
      type: Object,
      required: true
    }
  },
  methods: {
    goToFolder(folderId) {
      if (folderId) {
        this.currentFolderId = folderId;
        this.breadcrumbs.push({
          id: this.currentFolderId,
          title: this.$route.params.title
        })
      } else {
        this.currentFolderId = null;
        this.breadcrumbs = [];
      }
    },
    onItemClick(item) {
      if (this.config.inModal) {
        if (item.is_folder) {
          this.currentFolderId = item.object.id;
          this.breadcrumbs.push({
            id: this.currentFolderId,
            title: item.object.title
          })
        } else {
          this.setItemSelectionState(item.object.id, !this.selectedItemIds[item.object.id]);
        }
      }
    },
    onSelection(items) {
      this.selectedItemIds = {};
      items.forEach(item => {
        const id = parseInt(item.dataset.id);
        if (item.dataset.canEdit === '1') {
          this.selectedItemIds[id] = true;
        }
      });
    },
    onBulkUpdate() {
      this.selectedItemIds = {};
      this.$refs.list.load();
    },
    onBulkTransfer() {
      this.$emit('transfer', this.selectedItems);
      this.selectedItemIds = {};
    },
    onBulkUnselect() {
      this.selectedItemIds = {};
    },
    setItemSelectionState(id, state) {
      this.$set(this.selectedItemIds, id, state);
    },
    onDragStatusChange(status) {
      if (this.config.disableUpload) {
        return;
      }
      this.dragging = status;
    },
    onDrop(e) {
      if (this.config.disableUpload) {
        return;
      }
      this.dragging = false;
      if (e.dataTransfer && e.dataTransfer.files) {
        Array.from(e.dataTransfer.files).forEach(file => {
          this.$store.commit('uploads/addUpload', {
            file,
            data: {
              title: file.name.replace(/\.[^/.]+$/, ""),
              channel_id: this.channel.id,
              folder_id: this.currentFolderId,
            },
            external: false
          })
        });
      }
      e.preventDefault();
    },

    onItemEditClick(item) {
      if (item.is_folder) {
        this.$store.commit('modals/showStandardModal', {
          confirm: true,
          title: this.$t('dashboard.media.edit_folder'),
          buttonColor: '',
          buttonText: this.$t('global.save'),
          component: MediaManagerFolderEdit,
          formValues: {
            title: item.object.title,
            is_private: item.object.is_private,
          },
          fn: async (data) => {
            await this.$api.put(`/channels/${this.channel.id}/media/folders/${item.object.id}`, data);
            this.$refs.list.load();
          },
        })
      }
    },
    onItemDeleteClick(item) {
      if (item.is_folder) {
        this.$store.commit('modals/showStandardModal', {
          confirm: true,
          title: '',
          text: this.$t('dashboard.media.folders.confirm_delete'),
          component: MediaManagerFolderDeleteActions,
          formValues: {action: 'move_up'},
          fn: async ({action}) => {
            await this.$api.delete(`/channels/${this.channel.id}/media/folders/${item.object.id}?action=${action}`);
            this.$refs.list.load();
          },
        })
      } else {
        this.$store.commit('modals/showStandardModal', {
          confirm: true,
          title: this.$t('dashboard.media.delete.heading'),
          text: this.$t('dashboard.media.delete.text'),
          fn: async () => {
            await this.$api.delete(`/media/${item.object.id}`);
            this.$refs.list.load();
          },
        })
      }
    },
    onBreadcrumbsFolderClick(folder = null, index = null) {
      if (folder) {
        this.breadcrumbs.splice(index + 1, this.breadcrumbs.length - index);
      } else {
        this.breadcrumbs = [];
      }
      this.currentFolderId = folder ? folder.id : null;
      if (!this.config.disableLinks) {
        this.$router.push(`/dashboard/${this.channel.id}/media${(this.currentFolderId ? `/folder/${this.currentFolderId}` : '')}`);
      }
    },
    async loadBreadcrumbs() {
      if (!this.currentFolderId) return;
      this.breadcrumbs = await this.$api.get(`/channels/${this.channel.id}/media/folders/${this.currentFolderId}/breadcrumbs`);
    },
    startListeningToEvents() {
      this.$echo.private(`App.Dashboard.${this.channel.id}`)
        .listen('.media.deleted', () => {
          this.$refs.list?.load();
         this.$refs.disk_space?.load();
        })

        .listen('.media.convert_success', () => {
          this.$refs.list?.load();
        })
        .listen('.media.updated', ({media}) => {
          this.$refs.list?.updateItemIfExists(media);
        })
    }
  }
}
</script>
<style lang="scss">
.media-manager {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 3.5em);
  margin: -1em 0;
  @media screen and (max-width: 768px) {
    height: unset;
    margin: 0;
  }
  &--in-modal {
    height: 72.5vh;
    width: 100%;
    max-width: 90vw;
    margin: -1em;
  }
  &__header {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
  }


  &__dragover {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: stretch;
    justify-content: stretch;
    background: rgba(0, 0, 0, 0.55);
    z-index: 100000;
    pointer-events: none;

    &__title {
      font-size: 2em;
      margin: 1em;
      padding: 1em;
      width: 100%;
      border: .15em dashed;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 500;
    }
  }

  &__container {
    background: var(--box-color);
    position: relative;
    flex: 1;
  }

  &__items {

    .thumbs-list__heading {
      padding: 1em;
    }

    .thumbs-list__pager-container {
      bottom: 5.25em;
      left: unset;
      right: 1.25em;
      width: unset;
      font-size: .875em;
    }
  }


  &__bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
}
</style>
