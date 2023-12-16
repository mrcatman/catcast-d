<template>
  <div class="attachments">
    <div v-if="attachment.data" @click="showAttachmentModal($index)" class="attachment" v-for="(attachment,$index) in attachments"  :key="$index">
      <img :src="attachment.data.full_url" class="attachment__picture" v-if="attachment.attachment_type === 'picture'"/>
      <img :src="attachment.data.thumbnail" class="attachment__picture" v-else-if="attachment.attachment_type === 'video'"/>
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
    border-radius: .5em;
    overflow: hidden;
    height: 10em;
    cursor: pointer;
    margin: 0 1em 0 0;
    &__picture {
      height: 10em;
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
