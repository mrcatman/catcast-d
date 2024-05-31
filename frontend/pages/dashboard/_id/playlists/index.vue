<template>
  <c-box no-padding class="dashboard-page__playlists">
    <template slot="main">
      <c-thumbs-list ref="list" :config="listConfig">
        <template slot="filters">
          <c-select :options="orderOptions" v-model="order"></c-select>
        </template>
        <template slot="before_filters">
          <c-button @click="openCreatePlaylistModal()" color="green" icon="add_to_queue">{{$t('dashboard.playlists.add')}}</c-button>
        </template>
        <template slot="item" slot-scope="props">
          <c-list-item :to="`/dashboard/${channel.id}/playlists/${props.item.uuid}`" :picture="props.item.pictures_data.logo ? props.item.pictures_data.logo.full_url : null">
            <template slot="captions">
              <div class="list-item__title">
                {{ props.item.name }}
              </div>
              <div class="list-item__under-title">
                <c-statistics-icons :data="[
                      {icon: 'fa-play', value: props.item.media_count, margin: true},
                       {icon: 'remove_red_eye', value: props.item.views},
                       {icon: 'thumb_up', value: props.item.likes_count, margin: true},
                       {icon: 'fa-clock', value: formatPublishDate(props.item.updated_at, false)}
                      ]"></c-statistics-icons>
              </div>
            </template>
            <template slot="buttons">
              <c-button v-if="props.item.can_edit" @click="deleteProject(props.item)" color="red">
                {{ $t('global.delete') }}
              </c-button>
            </template>
          </c-list-item>
        </template>
      </c-thumbs-list>
    </template>
  </c-box>
</template>
<style lang="scss">
.dashboard-page__playlists {

}
</style>
<script>
import {formatPublishDate} from '@/helpers/dates.js';
import PlaylistsCreateModal from "@/components/dashboard/playlists/PlaylistsCreateModal";

export default {
  head() {
    return {
      title: this.$t('dashboard.playlists.heading')
    }
  },
  props: {
    channel: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      order: 'new',
    }
  },
  computed: {
    orderOptions() {
      return [
        {'name': this.$t('media.search.sort.new'), 'value': 'new'},
        {'name': this.$t('media.search.sort.old'), 'value': 'old'},
        {'name': this.$t('media.search.sort.popular'), 'value': 'popular'},
      ]
    },
    listConfig() {
      return {
        view: 'list',
        title: this.$t('dashboard.playlists.heading'),
        url: `/channels/${this.channel.id}/playlists/manager?order=${this.order}`,
        paginate: true,
        infiniteScroll: true,
        search: true,
        innerScroll: true,
        usePreloadingListItem: true,
        noPadding: true
      }
    }
  },
  methods: {
    formatPublishDate,
    openCreatePlaylistModal() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        title: this.$t('dashboard.playlists.add'),
        buttonColor: '',
        buttonText: this.$t('global.add'),
        component: PlaylistsCreateModal,
        formValues: {
          name: '',
          privacy_status: 2, // todo: codes
        },
        fn: async (data) => {
          const playlist = await this.$api.post('playlists', {
            ...data,
            channel_id: this.channel.id
          });
          this.$router.push(`/dashboard/${this.channel.id}/playlists/${playlist.uuid}`);
        },
      })
    },
    deleteProject(project) {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        title: this.$t('dashboard.playlists.delete_project.heading'),
        text: this.$t('dashboard.playlists.delete_project.text'),
        fn: async () => {
          await this.$api.delete('playlists/' + project.id);
          this.$refs.list.reload();
        },
      })
    },

  }
}
</script>
