<template>
  <div>
    <div class="channel-layout__description" v-html="descriptionHTML"></div>
    <div v-if="entity.links && entity.links.length > 0" class="buttons-row channel-layout__links">
      <c-button target="_blank" :href="link.url" :key="$index" v-for="(link, $index) in entity.links">{{link.name}}</c-button>
    </div>
    <div class="vertical-delimiter" v-if="entity.tags && entity.tags.length > 0"></div>
    <div class="channel-layout__tags" v-if="entity.tags && entity.tags.length > 0">
      <c-tag :key="$index" :to="`/${entityTagsType}/search?tags=${tag}`" v-for="(tag, $index) in entity.tags">{{tag}}</c-tag>
    </div>
  </div>
</template>
<script>
import marked from 'marked';
export default {
  computed: {
    descriptionHTML() {
      return this.entity.description?.trim().length ? marked.parse(this.entity.description) : this.$t('global.no_description');
    }
  },
  props: {
    entity: {
      type: Object,
      required: true
    },
    entityTagsType: {
      type: String,
      required: false
    }
  }
}
</script>
<style lang="scss" scoped>
.channel-layout {
  &__tags {
    margin-top: 1em;

  }
  &__description {
    line-height: 1.6;
  }
  &__links {
    margin-top: 1em;
    flex-wrap: wrap;
  }
}
</style>
