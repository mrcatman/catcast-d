<template>
  <div class="video-edit__panel__inputs">
   <div class="video-edit__panel__inner">
     <div class="video-edit__panel__header">
       <div class="video-edit__panel__title-container">
          <span class="video-edit__panel__file-name" v-if="filename">
           <i class="material-icons">attachment</i>
           <span class="video-edit__panel__file-name__text">
             {{filename}}
           </span>
         </span>
         <div class="modal__input-container">
           <c-input :placeholder="$t('dashboard.media.title')" :errors="errors.title" v-model="val.title" />
         </div>
       </div>
       <div v-if="add && val.picture" :style="val.picture ? `background: url('${val.picture}') no-repeat center center; background-size: cover;` : ''" class="video-edit__thumbnail"></div>
     </div>
     <div v-if="add" class="video-edit__panel__folder-name">
       {{$t('dashboard.media.folder')}} {{getFolderName}}
     </div>
     <div class="video-edit__panel__properties">
        <div class="video-edit__panel__thumbnail-container" v-if="!add">
          <c-picture-uploader :defaultValue="val.original_thumbnail" :title="$t('dashboard.media.thumbnail')" :data="{full_url: val.thumbnail}" folder="videos" v-model="val.preview_picture_id" />
        </div>
        <div class="modal__input-container">
          <c-input v-if="add" type="textarea"  :title="$t('dashboard.media.description')"  v-model="val.description"/>
          <c-text-editor v-else :title="$t('dashboard.media.description')"  v-model="val.description" />
        </div>
         <div class="row">
           <div class="col col--auto">
            <div class="modal__input-container">
              <c-checkbox :title="$t('dashboard.media.invisible')"  v-model="val.invisible"/>
            </div>
           </div>
           <div class="col col--auto" v-if="!val.invisible">
             <div class="modal__input-container">
               <c-checkbox :title="$t('dashboard.media.show_author')"  v-model="val.show_author"/>
             </div>
           </div>
         </div>
      </div>
     </div>
  </div>
</template>
<script>
  export default {
    props: {
      namesCache: {
        type: Object,
        required: false,
      },
      data: {
        type: Object,
        required: false,
      },
      add: {
        type: Boolean,
        required: false
      },
      value: {
        type: Object,
        required: true,
      }
    },
    computed: {
      getFolderName() {
        if (this.data.folder_id > 0) {
          return this.namesCache.folders[this.data.folder_id];
        }
        if (this.data.project_id > 0) {
          return this.namesCache.playlists[this.data.project_id];
        }
        return this.$t('dashboard.media.default_folder');
      }
    },
    mounted() {
      if (this.val.show_author === undefined) {
        this.val.show_author = true;
      }
    },
    data() {
      return {
        filename: (this.data && this.data._file) ? this.data._file.name : null,
        val: this.value,
        errors: {},
      }
    },
    watch: {
      val(newVal) {
        this.$emit('input', newVal);
      }
    }
  }
</script>
