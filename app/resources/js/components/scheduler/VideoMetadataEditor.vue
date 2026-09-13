<template>
  <c-modal :header="$t('scheduler.playlists.edit.heading')" v-model="isVisible">
    <div slot="main" >
      <div class="modal__input-container">
        <c-input :placeholder="$t('scheduler.playlists.edit.title')" :errors="errors.title" v-model="item.title" />
      </div>
      <div class="modal__input-container">
        <c-input type="textarea" :placeholder="$t('scheduler.playlists.edit.description')" :errors="errors.description" v-model="item.description" />
      </div>
      <div class="modal__input-container">
        <c-tags-input v-model="item.tags" :title="$t('scheduler.playlists.edit.tags')" />
      </div>
    </div>
    <div class="modal__buttons" slot="buttons">
      <div class="buttons-row">
        <c-button @click="savePlaylistItem()" >{{$t('global.save')}}</c-button>
      </div>
    </div>
  </c-modal>
</template>
<script>
  export default {
    watch: {
      value(newVal) {
        this.item = newVal;
      },
      visible(isVisible) {
        this.isVisible = isVisible;
      },
      isVisible(isVisible) {
        if (!isVisible) {
          this.$emit('close');
        }
      }
    },
    methods: {
      savePlaylistItem() {
        this.$emit('save', this.item);
        this.isVisible = false;
      }
    },
    data() {
      return {
        isVisible: this.visible,
        item: this.value,
        errors: {}
      }
    },
    props: {
      value: {
        type: Object,
        required: true
      },
      visible: {
        type: Boolean,
        required: true
      }
    }
  }
</script>
