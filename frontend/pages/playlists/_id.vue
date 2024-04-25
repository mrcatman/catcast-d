<template>
  <channel-layout class="playlist" :channel="channel" :playlist="playlist" >
    <template slot="main">
      <c-box>
        <template slot="main">
          <channel-entity-top-block
            :entity="playlist"
            entity-type="playlists"
            :channel="channel"
            :statistics="[{icon: 'remove_red_eye', value: playlist.views},{icon: 'fa-clock', value: formatPublishDate(playlist.created_at, false)} ]"
             :subscribe="true"
          />
        </template>
      </c-box>


          <c-box no-padding>
            <template slot="main">
              <c-tabs v-model="currentTab" :data="tabs"></c-tabs>
              <media-list v-if="currentTab === 'media'" :data="media" :url="`playlists/${playlist.uuid}/media`" :config="{search: true, queryParams: {order}}">
                <template slot="filters">
                  <c-select :options="orderOptions" v-model="order"></c-select>
                </template>
              </media-list>
              <div class="playlist-page__announces-section" v-else-if="currentTab === 'announces'">
                <div class="list-container" v-if="announces.data.length > 0">
                  <div class="list-container__inner">
                    <div class="list-item list-item--auto list-item--not-link" :key="$index" v-for="(announce, $index) in announces.data">
                      <div class="list-item__left">
                        <div class="list-item__captions list-item__captions--small">
                          <div class="list-item__title">
                            {{announce.title}}
                          </div>
                          <div class="list-item__sub">
                            {{announce.description}}
                          </div>
                        </div>
                      </div>
                      <div>
                        <div class="list-item__date">{{formatFullDate(announce.time, true, false)}}</div>
                        <AnnounceSubscribeButton :data="announce"/>
                      </div>
                    </div>
                  </div>
                </div>
                <c-nothing-found v-else/>
              </div>
            </template>
          </c-box>

          <comments-list entity-type="playlists" :entity-id="playlist.id" :entity-uuid="playlist.uuid"  />
       </template>

    <template slot="sidebar">
          <div class="playlist-page__box playlist-page__box--info">
            <nuxt-link :to="'/playlists/'+playlist.id" @click.native="goToplaylist()" class="list-item list-item--big list-item--no-height">
              <div class="list-item__left list-item__full-width">
                <div class="list-item__picture-block" :style="{backgroundImage: 'url('+playlist.logo+')'}" ></div>
                <div class="list-item__captions list-item__captions--small">
                  <div class="list-item__title">
                    {{playlist.name}}
                  </div>
                  <div class="list-item__under-title playlist-page__short-description">
                    {{playlist.short_description}}
                  </div>
                </div>
              </div>
            </nuxt-link>
          </div>

          <div class="playlist-page__box playlist-page__recommended-materials" >
            recommended videos
          </div>
    </template>
  </channel-layout>
</template>
<script>
  import ChannelEntityTopBlock from "@/components/channel/EntityTopBlock";
  import AnnounceSubscribeButton from "@/components/buttons/AnnounceSubscribeButton.vue";
  import {formatFullDate, formatPublishDate} from "@/helpers/dates";
  import ChannelLayout from "@/components/ChannelLayout";
  import MediaList from "@/components/MediaList";
  import CommentsList from "@/components/comments/CommentsList";

  export default {
    components: {
      ChannelEntityTopBlock,
      CommentsList,
      MediaList,
      ChannelLayout,
      AnnounceSubscribeButton,
    },
    async asyncData({app,params}) {
      const playlist = await app.$api.get(`/playlists/${params.id}`);
      const media = (await app.$api.get(`/playlists/${params.id}/media`));
      return {
        playlist,
        channel: playlist.channel,
        media,
        announces: [],
      }
    },
    head() {
      return {
        title: this.playlist.name
      }
    },
    data() {
      return {
        currentTab: 'media',
        order: 'new',
        orderOptions: [
          {
            name: this.$t('videos.search.sort.new'),
            value: 'new'
          },
          {
            name: this.$t('videos.search.sort.old'),
            value: 'old'
          },
          {
            name: this.$t('videos.search.sort.most_watched'),
            value: 'most_watched'
          },
          {
            name: this.$t('videos.search.sort.best'),
            value: 'best'
          },
        ],
      }
    },
    computed: {
      tabs() {
        return [
          {id: 'media', name: this.$t('playlists.tabs.media')},
          {id: 'announces', name: this.$t('playlists.tabs.announces')}
        ]
      },
      getBannerLink() {
        if (this.playlist) {
          return '/playlists/' + this.playlist.id;
        }

        if (this.channel) {
          return '/' + this.channel.shortname;
        }
        return null;
      }
    },
    methods: {
      formatFullDate,
      formatPublishDate,
    }
  }
</script>
