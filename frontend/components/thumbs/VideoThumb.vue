<template>
  <common-thumb class="media-thumb" :link="!config.disableLinks ? link : null" :picture="picture" :highlighted="config.highlighted">
    <template slot="inside_picture">
      <!--
      <div class="media-thumb__viewers">
        <i class="material-icons">remove_red_eye</i>
        <span class="channel-thumb__viewers__text">{{data.views}}</span>
      </div>
      -->
      <span v-if="data.duration > 0" class="media-thumb__duration">{{formatDuration(data.duration)}}</span>
      <c-tag color="green" v-if="isRecord">{{$t('media.record')}}</c-tag>
    </template>
    <template slot="texts">
      <div class="thumb__title">{{data.title}}</div>
      <channel-logo-and-name :channel="data.channel" v-if="data.channel && showParts.channel" :disableLinks="config.disableLinks" class="media-thumb__channel-logo-and-name" />
      <div class="thumb__tags" v-if="tags.length && showParts.tags">
        <c-tag :to="`/videos/search?tags=${tag}`" v-for="tag in tags" :key="tag">{{tag}}</c-tag>
      </div>
      <c-statistics-icons class="thumb__statistics-icons" :data="metadata"></c-statistics-icons>
    </template>
    <template slot="list_texts">
      <div class="thumb__description" v-if="showParts.description">{{data.description}}</div>
      <div class="thumb__tags thumb__tags--list" v-if="tags.length && showParts.tags">
        <c-tag :to="`/videos/search?tags=${tag}`" v-for="tag in tags" :key="tag">{{tag}}</c-tag>
      </div>
    </template>
  </common-thumb>
</template>
<style lang="scss">
  .media-thumb {
    &__viewers {
      position: absolute;
      right: .5em;
      top: .5em;
      display: flex;
      align-items: center;
      font-size: .75em;
      opacity: .5;
      &__text {
        margin-left: .5em;
        font-weight: 600;
      }
    }
    &__duration {
      position: absolute;
      left: .5em;
      bottom: 1em;
      font-size: .875em;
      padding: .25em .5em;
      border-radius: .25em;
      background: var(--darken-3);
    }

    &__channel-logo-and-name {
      margin: 1em 0 .5em;
      font-size: .75em;
    }
  }

  .view-list .media-thumb__viewers {
    display: none;
  }
</style>
<script>
import { mapGetters } from "vuex";
  import { formatPublishDate, formatDuration } from '@/helpers/dates.js';

  import CommonThumb from "./CommonThumb";
  import ChannelLogoAndName from "@/components/ChannelLogoAndName";
  import { MEDIA_SOURCE_TYPE_RECORD } from "@/constants/entity-types";

  export default {
    components: {ChannelLogoAndName, CommonThumb},
    methods: {
      formatPublishDate,
      formatDuration
    },
    computed: {
      ...mapGetters('config', ['siteLogoSquare']),
      isRecord() {
        return this.data.source_type_name === MEDIA_SOURCE_TYPE_RECORD;
      },
      showParts() {
        return this.config?.showParts || {
          description: true,
          channel: true,
          tags: true
        };
      },
      metadata() {
        return [
          {
            icon: 'fa-clock', value: formatPublishDate(this.data.created_at), tooltip: this.$t('media.created_at')
          },
          {
            icon: 'fa-eye', value: this.data.views, tooltip: this.$t('media.views')
          }
        ];
      },
      picture() {
        return this.data.thumbnail?.full_url;
      },
      link() {
        return `${this.data.local_url}${this.data.index_in_playlist >= 0 ? (`?playlist_id=${this.data.playlist_id}&index=${this.data.index_in_playlist}`) : ''}`;
      },
      tags() {
        const tags = this.data.tags || [];
        return tags.slice(0, 3);
      },
      description() {
        let description = this.data.description;
        if (!description) {
          return "";
        }
        return description.replace(/<[^>]*>?/gm, '');
      }
    },
    props: {
      data: {
        type: Object,
        required: true
      },
      big: Boolean,
      config: {
        type: Object,
        default() {
          return {}
        }
      },
    }
  }
</script>
