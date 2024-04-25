<template>
  <common-thumb class="channel-thumb" :link="{path: link, params: {channel: data}}" :picture="picture" :logo="data.logo">
    <template slot="inside_picture">
      <span class="channel-thumb__badge channel-thumb__badge--live" v-if="data.active_broadcast">{{$t('channel.broadcast.online')}}</span>
      <span class="channel-thumb__badge channel-thumb__badge--offline" v-else>{{$t('channel.broadcast.offline')}}</span>
      <div class="channel-thumb__viewers" v-if="data.active_broadcast && data.active_broadcast.viewers !== null">
        <i class="material-icons">remove_red_eye</i>
        <span class="channel-thumb__viewers__text">{{data.active_broadcast.viewers}}</span>
      </div>
    </template>
    <template slot="texts">
      <div class="thumb__title" v-if="!data.active_broadcast">{{data.name}}</div>
      <div class="thumb__title" v-else>{{data.active_broadcast.title}}</div>
      <div class="thumb__small-title" v-if="data.active_broadcast">{{data.name}}</div>
      <div class="thumb__tags" v-if="tags.length">
        <c-tag :to="`/tv/search?tags=${tag}`" v-for="tag in tags" :key="tag">{{tag}}</c-tag>
      </div>
    </template>
    <template slot="list_texts">
      <c-statistics-icons :data="metadata"></c-statistics-icons>
      <div class="thumb__description">{{data.description}}</div>
      <div class="thumb__tags thumb__tags--list" v-if="tags.length">
        <c-tag :to="`/tv/search?tags=${tag}`" v-for="tag in tags" :key="tag">{{tag}}</c-tag>
      </div>
    </template>
  </common-thumb>
</template>
<style lang="scss">
  .channel-thumb {
    &__badge {
      position: absolute;
      top: .5em;
      left: .5em;
      z-index: 1000;
      font-weight: 600;
      padding: .25em .5em;
      border-radius: .25em;
      opacity: .75;
      font-size: .75em;
      &--live {
        background: var(--red);
      }
      &--offline {
        background: var(--darken-3);
      }
    }
    &__stream-name {
      position: absolute;
      bottom: 0;
      left: 0;
      background: rgba(0, 0, 0, 0.5);
      padding: .5em;
      width: calc(100% - 1em);
      white-space: nowrap;
      font-size: .875em;
      overflow: hidden;
      text-overflow: ellipsis;
    }
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
    .statistics-icons {
      margin-top: .75em;
    }
  }
</style>
<script>
  import { formatPublishDate } from '@/helpers/dates.js';
  import {mapGetters} from "vuex";
  import CommonThumb from "./CommonThumb";

  export default {
    components: {CommonThumb},
    methods: {
      formatPublishDate
    },
    computed: {
      ...mapGetters('config', ['siteLogoSquare']),
      metadata() {
        let metadata = [];
        if (this.data.user) {
          metadata.push({
            icon: 'fa-user', value: this.data.user.username, link: `/users/${this.data.user.id}`, tooltip: this.$t('channel.owner')
          });
        }
        if (this.data.last_online_at) {
          metadata.push({
            icon: 'fa-clock', value: formatPublishDate(this.data.last_online_at), tooltip: this.$t('channel.last_online_at')
          });
        }
        return metadata;
      },
      picture() {
        return this.data.active_broadcast?.thumbnail_url || this.data.logo;
      },
      link() {
        return `/${this.data.shortname}`;
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
    }
  }
</script>
