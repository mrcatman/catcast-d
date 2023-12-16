<template>
  <c-modal v-model="visible">
    <div class="attachments-modal">
      <div class="attachments-modal__arrow attachments-modal__arrow--left" v-if="attachments.list.length > 1">
        <c-button @click="changeIndex(-1)" :disabled="attachments.index === 0" transparent icon-only icon="arrow_back_ios"/>
      </div>
      <div class="attachments-modal__content" v-if="attachments.list[attachments.index]">
        <div class="attachments-modal__picture-container" v-if="attachments.list[attachments.index].attachment_type === 'picture'">
          <img :src="attachments.list[attachments.index].data.full_url" class="attachments-modal__picture" />
        </div>
        <div class="attachments-modal__media-container"  v-else-if="attachments.list[attachments.index].attachment_type === 'media'">
          <media-player :channel="attachments.list[attachments.index].data.channel" :media="attachments.list[attachments.index].data"/>
        </div>
      </div>
      <div class="attachments-modal__arrow attachments-modal__arrow--right" v-if="attachments.list.length > 1">
        <c-button @click="changeIndex(1)" :disabled="attachments.index >= attachments.list.length - 1" transparent icon-only icon="arrow_forward_ios"/>
      </div>
    </div>
  </c-modal>
</template>
<style lang="scss">
  .attachments-modal {
    display: flex;
    align-items: center;
    text-align: center;
    justify-content: center;
    &__arrow {
      position: fixed;
      top: 0;
      padding: 1em;
      height: 100%;
      box-sizing: border-box;
      display: flex;
      align-items: center;
      font-size: 1.325em;
      &--left {
        left: 0;
      }

      &--right {
        right: 0;
      }
    }


    &__picture {
      height: 100%;
      max-height: 60vh;
    }
    &__media-container {
      position: relative;
      width: 70em;
      height: 50em;
    }
  }
</style>
<script>
  import {mapState} from 'vuex';
  import MediaPlayer from "@/components/media-player/MediaPlayer";
  export default {
    components: {MediaPlayer},
    watch: {
      visible(visible) {
        if (!visible) {
          this.$store.commit('modals/attachmentsModal', {visible});
        }
      },
      'attachments.visible'(visible) {
        this.visible = visible;
      }
    },
    data() {
      return {
        visible: false,
      }
    },
    computed: {
      ...mapState('modals', ['attachments'])
    },
    methods: {
      changeIndex(i) {
        let index = this.attachments.index;
         if ((index + i >= 0) && (index + i < this.attachments.list.length )) {
          let newIndex = index + i;
          this.$store.commit('modals/attachmentsModalIndex', {index: newIndex});
        }
      }
    }
  }
</script>
