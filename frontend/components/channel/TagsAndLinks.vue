<template>
  <div>
    <div class="channel-layout__description" v-html="descriptionHTML"></div>
    <div v-if="entity.links && entity.links.length > 0" class="buttons-row channel-layout__links">
      <c-button target="_blank" :href="link.url" :key="$index" v-for="(link, $index) in entity.links">{{link.title}}</c-button>
    </div>
    <div class="vertical-delimiter" v-if="entity.tags && entity.tags.length > 0"></div>
    <div class="channel-layout__tags" v-if="entity.tags && entity.tags.length > 0">
      <c-tag :key="$index" :to="`/directory/search?tags=${tag}`" v-for="(tag, $index) in entity.tags">{{tag}}</c-tag>
    </div>
  </div>
</template>
<script>
import { parseMarkdown } from "@/helpers/markdown";

export default {
  computed: {
    descriptionHTML() {
      return this.entity.description?.trim().length ? parseMarkdown(this.entity.description) : this.$t('global.no_description');
    }
  },
  props: {
    entity: {
      type: Object,
      required: true
    },
  }
}
</script>
<style lang="scss" scoped>
.channel-layout {
  &__tags {
    margin: 1em -.5em 0;
  }
  &__description {
    line-height: 1.6;
    h1, h2, h3, h4, h5 {
      margin: 0.125em 0;
    }
  }
  &__links {
    margin-top: 1em;
    flex-wrap: wrap;
  }
}
</style>
