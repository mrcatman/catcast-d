<template>
  <div class="channel-layout__info">
    <tags-and-links :entity="channel" />
  </div>
</template>
<style lang="scss">
.channel-layout {
  &__info {
    padding: 1em;
  }
}
</style>
<script>
import TagsAndLinks from "@/components/channel/TagsAndLinks.vue";
export default {
  components: {
    TagsAndLinks
  },
  props: {
    channel: {
      type: Object,
      required: true,
    },
  }
}
</script>
