<template>
  <div class="channel-layout__entity-top-block">
    <c-row>
      <c-col auto-width  v-if="entity.logo" >
        <div class="channel-layout__entity-top-block__logo" :style="{backgroundImage :'url('+entity.logo+')'}"></div>
      </c-col>
      <c-col>
        <c-row justify="between">
          <c-col>
            <div class="channel-layout__entity-top-block__title">{{ entity.title || entity.name }}</div>
            <c-statistics-icons class="channel-layout__entity-top-block__statistics" :data="statistics" />
          </c-col>
          <c-col auto-width>
            <div class="buttons-row">
              <rating :entity-type="entityType" :entity-id="entity.id" :entity-uuid="entity.uuid" />
              <subscribe-button v-if="subscribe" :entity-id="entity.id" :entity-type="entityType" :entity-uuid="entity.uuid" />
            </div>
          </c-col>
        </c-row>
      </c-col>
    </c-row>
    <div class="vertical-delimiter"></div>
    <c-row>
      <c-col auto-width>
       <channel-logo-and-name :channel="channel"/>
      </c-col>
    </c-row>
    <tags-and-links :entity="entity" :entity-tags-type="entityTagsType || entityType" />
  </div>
</template>
<script>
import ChannelLogoAndName from "@/components/ChannelLogoAndName";
import LikesButton from "@/components/buttons/LikesButton";
import Rating from "@/components/Rating";
import SubscribeButton from "@/components/buttons/SubscribeButton.vue";
import TagsAndLinks from "@/components/channel/TagsAndLinks.vue";
export default {

  components: {TagsAndLinks, SubscribeButton, Rating, LikesButton, ChannelLogoAndName},
  props: {
    entity: {
      type: Object,
      required: true
    },
    entityType: {
      type: String,
      required: true
    },
    entityTagsType: {
      type: String,
      required: false
    },
    channel: {
      type: Object,
      required: true
    },
    statistics: {
      type: Array,
      required: false,
      default: () => {
        return []
      }
    },
  }
}
</script>
<style lang="scss" scoped>
.channel-layout__entity-top-block {
  font-size: .875em;
  &__title {
    margin-bottom: .5em;
    font-size: 1.125em;
    line-height: 1.6;
    font-weight: 600;
    color: var(--channel-colors-page-headings);
  }
  &__logo {
    width: 7em;
    min-width: 7em;
    height: 4em;
    background-size: cover;
    background-position: center;
    margin-right: 1em;
  }

  &__statistics {
    font-size: 1em;
  }
}
</style>
