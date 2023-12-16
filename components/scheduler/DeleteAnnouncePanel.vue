<template>
  <c-modal v-model="isVisible">
    <div slot="main" >
      <div class="modal__text">
        {{$t('scheduler.delete_announce_text')}}
      </div>
    </div>
    <div class="modal__buttons" slot="buttons">
      <div class="buttons-row">
        <c-button @click="continueDeletingAnnounce()" :loading="deleteAnnouncePanel.loading">{{$t('global.ok')}}</c-button>
        <c-button flat @click="isVisible = false">{{$t('global.cancel')}}</c-button>
      </div>
    </div>
  </c-modal>
</template>
<script>
  export default {
    methods: {
      continueDeletingAnnounce() {
        this.deleteAnnouncePanel.loading = true;
        this.$axios.delete(`timetable/announces/${this.deleteAnnouncePanel.data.id}`).then (res => {
          this.deleteAnnouncePanel.loading = false;
          this.$store.commit('NEW_ALERT', res.data);
          if (res.data.status) {
            this.deleteAnnouncePanel.visible = false;
            this.isVisible = false;
            this.$emit('close');
            this.$emit('deleted', this.deleteAnnouncePanel.data);
          }
        })
      },
    },
    data() {
      return {
        isVisible: this.visible,
        deleteAnnouncePanel: {
          loading: false,
          data: this.value,
        }
      }
    },
    watch: {
      value(newVal) {
        this.deleteAnnouncePanel.data = newVal;
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
    props: {
     visible: {
        type: Boolean,
        required: true
      },
      value: {
        type: Object,
        required: true
      }
    },
  }
</script>
