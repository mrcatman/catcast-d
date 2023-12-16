<template>
  <comments-list entity-type="channels" :entity-id="channel.id"  />
</template>
<script>
  import CommentsList from '@/components/comments/CommentsList';
  export default {
    computed: {

    },
    components: {
      CommentsList
    },
    props: {
      accessSettings: {
        type: Object,
        required: true
      },
      channel: {
        type: Object,
        required: true,
      }
    },
  }
</script>
