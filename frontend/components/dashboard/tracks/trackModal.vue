<template>
  <c-modal v-model="val" :header="$t('dashboard.tracks._form.edit_track')" :showCloseButton="true">
    <div slot="main">
      <div class="modal__input-container">
        <c-input :errors="errors.title" :title="$t('dashboard.tracks._form.title')" v-model="editData.title" />
      </div>
      <div class="modal__input-container">
        <c-input :errors="errors.author" :title="$t('dashboard.tracks._form.author')" v-model="editData.author" />
      </div>
      <div class="modal__input-container">
        <c-input :errors="errors.album" :title="$t('dashboard.tracks._form.album')" v-model="editData.album" />
      </div>
      <div class="modal__input-container">
        <c-tags-input :errors="errors.tags" :title="$t('dashboard.tracks._form.tags')" v-model="editData.tags" />
      </div>
      <div v-show="isInPublicFolder" class="modal__input-container">
        <c-text-editor :errors="errors.description" :title="$t('dashboard.tracks._form.description')" v-model="editData.description" />
      </div>
      <div v-show="!isInPublicFolder" class="modal__input-container">
        <c-checkbox :errors="errors.is_public" :title="$t('dashboard.tracks._form.is_public')" v-model="editData.is_public" />
      </div>
    </div>
    <div class="modal__buttons" slot="buttons">
      <div class="buttons-row">
        <c-button :loading="loading" @click="editTrack()">{{$t('dashboard.tracks._form.save_track_button')}}</c-button>
        <c-button color="red" @click="val = false">{{$t('global.cancel')}}</c-button>
      </div>
   </div>
  </c-modal>
</template>
<style lang="scss">

</style>
<script>
  export default {
    watch: {
      val(newVal) {
        this.$emit('input', newVal);
      },
      data(newData) {
        this.editData = newData;
      }
    },
    props: {
      isInPublicFolder: {
        type: Boolean,
        required: false,
      },
      value: {
        type: Boolean,
        required: true
      },
      data: {
        required: true,
      },
    },
    mounted() {

    },
    data () {
      return {
        val: this.value,
        response: null,
        loading: false,
        errors: {},
        currentEditingId: null,
        editData: this.data,
      }
    },

    methods: {

      editTrack() {
        this.loading = true;
        this.$axios.put('/tracks/'+this.editData.id,this.editData).then(res => {
          this.loading = false;
          this.$store.commit('NEW_ALERT',res.data);
          this.response = res.data;
          this.errors = res.data.errors || {};
          if (res.data.status) {
            this.$emit('editTrack', res.data.track);
            this.val = false;
          }
        })
      },
    }
  }
</script>
