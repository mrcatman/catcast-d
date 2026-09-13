<template>
  <div>
    <c-modal v-model="isVisible">
    <div slot="main" class="announce-panel">
      <div class="announce-panel__inputs">
        <c-response :data="announcePanel.response" />
        <div class="row row--centered">
          <div class="col">
            <div class="modal__input-container">
              <c-datetime-picker :title="$t('scheduler.announces.time')" v-model="announcePanel.data.time" :errors="announcePanel.errors.time" />
            </div>
          </div>
          <div class="col">
            <div class="modal__input-container">
              <c-input type="number" :min="1" :max="1440" :title="$t('scheduler.announces.length')" :errors="announcePanel.errors.length" v-model="announcePanel.data.length" />
            </div>
          </div>
        </div>
        <div class="modal__input-container">
          <c-input :title="$t('scheduler.announces.title')" v-model="announcePanel.data.title" :errors="announcePanel.errors.title" />
        </div>
        <div class="modal__input-container">
          <c-input type="textarea" :title="$t('scheduler.announces.description')" v-model="announcePanel.data.description" :errors="announcePanel.errors.description" />
        </div>
        <div class="row row--centered">
          <div class="col" v-if="isChannelAdmin">
            <div class="modal__input-container" >
              <c-select :showEmptyVariant="false" :title="$t('scheduler.announces.user')" :options="broadcastingUsers" v-model="announcePanel.data.user_id" :errors="announcePanel.errors.user_id"  />
            </div>
          </div>
          <div class="col">
            <div class="modal__input-container">
              <c-tags-input v-model="announcePanel.data.tags" :title="$t('scheduler.announces.tags')" />
            </div>
          </div>
        </div>
        <div class="row row--centered">
          <div class="col">
            <div class="modal__input-container">
              <c-picture-uploader :title="$t('scheduler.announces.picture')" :data="(announcePanel.data.pictures_data && announcePanel.data.pictures_data.cover) ? announcePanel.data.pictures_data.cover : {}" class="filepicker--video-cover" folder="announces" v-model="announcePanel.data.cover" />
            </div>
          </div>
          <div class="col">
            <div class="modal__input-container">
              <c-select :showEmptyVariant="false" :title="$t('scheduler.announces.project')" :options="playlistsList" v-model="announcePanel.data.project_id" :errors="announcePanel.errors.project_id"  />
            </div>
            <div class="modal__input-container">
              <c-checkbox :title="$t('scheduler.announces.need_to_record')" v-model="announcePanel.data.need_to_record" />
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal__buttons" slot="buttons">
      <div class="buttons-row">
        <c-button :loading="announcePanel.loading" @click="saveAnnounce()">{{isEditing? $t('global.save') : $t('global.add')}}</c-button>
        <c-button flat @click="announcePanel.visible = false">{{$t('global.cancel')}}</c-button>
        <div class="buttons-row__right" v-if="announcePanel.data.id">
          <c-button color="red" @click="deleteCurrentAnnounce()">{{$t('global.delete')}}</c-button>
        </div>
      </div>
    </div>
  </c-modal>

   <DeleteAnnouncePanel @close="() => deleteAnnouncePanel.visible = false" @deleted="onDeleted" :visible="deleteAnnouncePanel.visible" v-model="deleteAnnouncePanel.data"/>

  </div>
</template>
<script>
  import DeleteAnnouncePanel from '@/components/scheduler/DeleteAnnouncePanel';
  export default {
    components: {
      DeleteAnnouncePanel
    },
    watch: {
      value(newVal) {
        this.announcePanel.data = newVal;
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
      onDeleted(data) {
        this.isVisible = false;
        this.$emit('close');
        this.$emit('deleted', data);
      },
      deleteCurrentAnnounce() {
        this.deleteAnnouncePanel.data = this.announcePanel.data;
        this.deleteAnnouncePanel.visible = true;
      },
      saveAnnounce() {
        this.announcePanel.loading = true;
        let data = this.announcePanel.data;
        data.channel_id = this.channel.id;
        this.$axios({
          method: this.isEditing ? 'PUT' : 'POST',
          url: this.isEditing ? `timetable/announces/${this.announcePanel.data.id}` : `timetable/announces`,
          data
        }).then(res => {
          this.announcePanel.response = res.data;
          this.announcePanel.loading = false;
          if (res.data.status) {
            this.$emit('save', res.data.data.announce);
            this.$store.commit('NEW_ALERT', res.data);
            this.announcePanel.errors = {};
            this.announcePanel.visible = false;
            this.announcePanel.response = null;
            this.isVisible = false;
            this.$emit('close');
          } else {
            this.announcePanel.errors = res.data.errors || {};
          }

        })
      },
    },
    data() {
      return {
        isVisible: this.visible,
        deleteAnnouncePanel: {
          visible: false,
          loading: false,
          data: {},
          errors: {}
        },
        announcePanel: {
          response: null,
          loading: false,
          data: this.value,
          pictures_data: null,
          errors: {}
        }
      }
    },
    props: {
      isEditing: {
        type: Boolean,
        required: true
      },
      broadcastingUsers: {
        type: Array,
        required: true
      },
      playlistsList: {
        type: Array,
        required: true
      },
      isChannelAdmin: {
        type: Boolean,
        required: true
      },
      visible: {
        type: Boolean,
        required: true
      },
      channel: {
        type: Object,
        required: true
      },
      value: {
        type: Object,
        required: true
      }
    },
  }
</script>
