<template>
  <div class="announces-manager">

    <announcePanel @close="() => announcePanel.visible = false" :isEditing="announcePanel.isEditing" :broadcastingUsers="broadcastingUsers" :playlistsList="playlistsList" :isChannelAdmin="isChannelAdmin" :canAddAnnounces="canAddAnnounces" :visible="announcePanel.visible" @save="onAnnounceSaved" @deleted="onAnnounceDeleted" :channel="channel" v-model="announcePanel.data"/>
    <DeleteAnnouncePanel @deleted="onAnnounceDeleted" @close="() => deleteAnnouncePanel.visible = false" :visible="deleteAnnouncePanel.visible" v-model="deleteAnnouncePanel.data"/>

    <!--
    <c-modal v-model="deleteAnnouncePanel.visible">
      <div slot="main" >
        <div class="modal__text">
          {{$t('scheduler.delete_announce_text')}}
        </div>
      </div>
      <div class="modal__buttons" slot="buttons">
        <div class="buttons-row">
          <c-button @click="continueDeletingAnnounce()" :loading="deleteAnnouncePanel.loading">{{$t('global.ok')}}</c-button>
          <c-button flat @click="deleteAnnouncePanel.visible = false">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>
  -->

    <div class="announces-manager__items">
      <div class="announces-manager__item" :key="$index" v-for="(announce, $index) in announces">
        <div  class="announces-manager__item__header">
          <a v-if="announce.can_edit" @click="editAnnounce(announce)" class="announces-manager__item__header__button">
            <i class="fa fa-edit"></i>
          </a>
          <div class="announces-manager__item__title-container">
            <span class="announces-manager__item__time">{{formatFullDate(announce.time)}}</span>
            <span class="announces-manager__item__title">{{announce.title}}</span>
          </div>
          <a v-if="announce.can_edit" @click="deleteAnnounce(announce)" class="announces-manager__item__header__button">
            <i class="fa fa-times"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .announces-manager {
    background: linear-gradient(90deg, rgba(255, 255, 255, .01176), rgba(255, 255, 255, .1098));
    &__item {
      margin: 1em;
      &__header {
        padding: .5em;
        font-weight: 500;
        text-align: center;
        background: var(--active-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        &__button {
          cursor: pointer;
        }
      }

      &__time {
        font-size: .875em;
        font-weight: 300;
        margin: 0 .5em 0 0;
      }

      &__title-container {
        text-align: center;
        flex: 1;
        display: flex;
        flex-direction: column;
      }


    }
  }
</style>
<script>
  import announcePanel from '@/components/scheduler/announcePanel';
  import DeleteAnnouncePanel from '@/components/scheduler/DeleteAnnouncePanel';
  import { getTime, formatFullDate, getDate } from '@/helpers/dates.js';

  let defaultAnnounceData = {
    length: 30,
    time: Math.floor(new Date().getTime() / 1000),
    title: '',
    description: '',
    user_id: null,
    cover: null,
    tags: [],
  };

  export default {
    watch: {
      value(newVal) {
        this.announces = newVal;
      }
    },
    components: {
      DeleteAnnouncePanel,
      announcePanel
    },
    methods: {
      editAnnounce(announce) {
        if (this.canAddAnnounces) {
          this.announcePanel.isEditing = true;
          let data = JSON.parse(JSON.stringify(announce));
          if (announce.pictures_data && announce.pictures_data.cover) {
            this.announcePanel.coverData = announce.pictures_data.cover;
            data.cover = announce.pictures_data.cover.id;
          }
          this.announcePanel.data = data;
          this.announcePanel.visible = true;
        }
      },
      formatFullDate,
      onAnnounceDeleted(deletedItem) {
        this.announces = this.announces.filter(announce => announce.id !== deletedItem.id);
        this.$emit('deleted', deletedItem);
      },
      onAnnounceSaved(newAnnounce) {
         if (this.announcePanel.isEditing) {
          this.announces = this.announces.filter(announce => announce.id !== newAnnounce.id);
        }
        this.announces.unshift(newAnnounce);
      },

      deleteAnnounce(announce) {
        this.deleteAnnouncePanel.data = announce;
        this.deleteAnnouncePanel.visible = true;
      },
      deleteCurrentAnnounce() {
        this.deleteAnnouncePanel.data = this.announcePanel.data;
        this.deleteAnnouncePanel.visible = true;
      },
    },
    data() {
      return {

        announces: this.value,
        deleteAnnouncePanel: {
          visible: false,
          loading: false,
          data: {},
          errors: {}
        },
        announcePanel: {
          response: null,
          visible: false,
          loading: false,
          isEditing: false,
          data: defaultAnnounceData,
          coverData: null,
          errors: {

          }
        },
      }
    },
    props: {
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
      canAddAnnounces: {
        type: Boolean,
        required: false
      },
      value: {
        type: [Object, Array],
        required: true
      },
      channel: {
        type: Object,
        required: true
      }
    }
  }
</script>
