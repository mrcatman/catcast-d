<template>
  <c-tabs class="dashboard-page__menu" :vertical="!isMobile" :data="tabs"/>
</template>
<script>
import isMobile from "@/helpers/isMobile";

const pages = [
  {id: 'statistics', permissions:  ['statistics'], name: 'dashboard.links.statistics', icon: 'show_chart'},
  {id: 'info', permissions: ['edit_info'], name: 'dashboard.links.info', icon: 'fa-info-circle'},
  {id: 'broadcasts/settings',  permissions: ['live_broadcast'], name: 'dashboard.links.broadcasts_settings', icon: 'live_tv'},
  {id: 'broadcasts/list',  permissions: ['live_broadcast'], name: 'dashboard.links.broadcasts_list', icon: 'fa-tower-broadcast'},
  {id: 'subscribers', name: 'dashboard.links.subscribers', icon: 'fa-users'},
  {id: 'chat',  permissions: ['edit_info'], name: 'dashboard.links.chat', icon: 'chat'},
  {id: 'design',  permissions: ['edit_info'], name: 'dashboard.links.design', icon: 'dashboard'},
  {id: 'team', permissions: [], name: 'dashboard.links.team', icon: 'fas-pen-fancy'},
  {id: 'banlist', permissions: ['moderation'], name: 'dashboard.links.banlist', icon: 'fa-ban'},
  {id: 'media', permissions: ['media'], name: 'dashboard.links.media', icon: 'fa-play'},
  {id: 'playlists', permissions: ['playlists'], name: 'dashboard.links.playlists', icon: 'fa-id-card'},

];

export default {
  computed: {
    tabs() {
      const permissions = Object.keys(this.permissions);
      return pages.filter(page => {
        if (!page.permissions) {
          return true;
        }
        const pagePermissions = [...page.permissions, 'admin', 'owner', 'channel_admin'];
        return permissions.filter(permission => pagePermissions.includes(permission)).length !== 0;
      }).map(page => {
        return {
          id: page.id,
          name: this.$t(page.name),
          link: `/dashboard/${this.channel.id}/${page.id}`,
          icon: page.icon,
        }
      })
    }
  },
  data() {
    return {
      visible: false,
      isMobile: isMobile()
    }
  },
	props: {
    channel: {
			type: Object,
			required: true
		},
		permissions: {
			type: Object,
			required: true
		}
	},
}
</script>
<style lang="scss">
.dashboard-page {
  &__menu {
    height: calc(100% - 4.5em);
    display: flex;
    align-items: center;
    overflow: auto;
    @media screen and (max-width: 768px) {
      height: unset;
    }
  }
}
</style>
