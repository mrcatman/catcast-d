<template>
  <div class="attachments">
    <div v-if="attachment.data" @click="showAttachmentModal($index)" class="attachment" v-for="(attachment,$index) in attachments"  :key="$index">
      <c-icon class="attachment__play-icon" icon="fa-play" v-if="attachment.attachment_type === 'media'" />
      <img :src="attachment.data.full_url" class="attachment__picture" v-if="attachment.attachment_type === 'picture'"/>
      <img :src="attachment.data.thumbnail?.full_url" class="attachment__picture" v-else-if="attachment.attachment_type === 'media'"/>
    </div>
  </div>
</template>
<style lang="scss">
  .attachments {
    margin: 1em 0;
    display: flex;
    flex-wrap: wrap;
  }
  .attachment {
    border-radius: .25em;
    overflow: hidden;
    min-width: 10em;
    height: 10em;
    cursor: pointer;
    margin-right: .5em;
    background: var(--darken-2);
    position: relative;
    &__picture {
      height: 100%;
    }
    &__play-icon {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform .2s;
      &:hover {
        transform: scale(1.2);
      }
    }
  }
</style>
<script>
  export default {
    props: {
      attachments: {
        type: Array,
        required: true
      }
    },
    methods: {
      showAttachmentModal(index) {
        this.$store.commit('modals/attachmentsModal', {visible: true, attachments: this.attachments, index})
      },
    }
  }
</script>
