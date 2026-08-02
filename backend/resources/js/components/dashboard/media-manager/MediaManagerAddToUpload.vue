<template>
  <div>
    <c-button flat rounded icon-only icon="fa-upload" @click="showUploadToolbar = !showUploadToolbar" />
    <c-popup-menu manual position="bottom-left" v-model="showUploadToolbar">
      <c-popup-menu-item :icon="isRadio ? 'fa-podcast' : 'fa-video'" @click="openFilePicker()">{{$t('dashboard.media.add_files')}}</c-popup-menu-item>
      <c-popup-menu-item icon="fa-globe" @click="openExternalPicker()">{{$t('dashboard.media.add_external')}}</c-popup-menu-item>
      <c-popup-menu-item :with-delimiter="true" icon="fa-folder" @click="addFolder()">{{ $t('dashboard.media.add_folder') }}</c-popup-menu-item>
    </c-popup-menu>
    <input @change="onFileInputChange" type="file" style="display:none" multiple ref="fileInput" :accept="isRadio ? 'audio/*' : 'video/*'"/>
  </div>
</template>
<script>
import MediaManagerAddExternal from "@/components/dashboard/media-manager/MediaManagerAddExternal";
import MediaManagerFolderEdit from "@/components/dashboard/media-manager/MediaManagerFolderEdit.vue";

export default  {
  computed: {
    isRadio() {
      return this.channel.is_radio;
    }
  },
  watch: {
    async 'externalMedia.visible'() {
      await this.$nextTick();
    //  this.$refs.external_video_input.focus();
    }
  },
  data() {
    return {
      externalMedia: {
        visible: false,
        url: '',
        loading: false,
        errors: null
      },
      showUploadToolbar: false,
    }
  },
  methods: {
    addFolder() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        title: this.$t('dashboard.media.add_folder'),
        buttonColor: '',
        buttonText: this.$t('global.add'),
        component: MediaManagerFolderEdit,
        formValues: {
          title: '',
          is_private: false,
        },
        fn: async (data) => {
          await this.$api.post(`/channels/${this.channel.id}/media/folders`, data);
          this.$emit('load');
        },
      })
    },
    openFilePicker() {
      this.$refs.fileInput.click();
    },
    openExternalPicker() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        title: this.$t('dashboard.media.add_external'),
        buttonColor: '',
        buttonText: this.$t('global.add'),
        component: MediaManagerAddExternal,
        formValues: {
          url: '',
        },
        fn: async ({url}) => {
          const externalMediaData = await this.$api.post(`/media/get-info-by-url`, {url});
          this.$store.commit('uploads/addUpload', {
            data: {
              title: externalMediaData.title,
              thumbnail: externalMediaData.thumbnail,
              channel_id: this.channel.id,
              folder_id: this.currentFolderId,
              url
            },
            external: true
          })
        },
      })
    },
    onFileInputChange(e) {
      if (e.target && e.target.files) {
        Array.from(e.target.files).forEach(file => {
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
    },
  },
  props: {
    channel: {
      type: Object,
      required: true
    },
    folderId: Number
  }
}
</script>

