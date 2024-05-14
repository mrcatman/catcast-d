<template>
<div class="top-bar">
	<div class="top-bar__left">
		<c-button class="top-bar__menu-toggle" flat rounded icon="menu" @click="TOGGLE_SIDEBAR"></c-button>
		<nuxt-link class="site-logo" to="/">
			<img :src="siteLogoSquare" :alt="siteName" class="site-logo__picture" />
      <img :src="siteLogo" :alt="siteName"  class="site-logo__picture site-logo__picture--big" />
		</nuxt-link>
	</div>
	<div class="top-bar__center">
    <site-search />
  </div>
	<div class="top-bar__right">
		<user-panel />
	</div>
</div>
</template>
<script>
import {mapGetters, mapMutations} from 'vuex';

import UserPanel from '@/components/layout/UserPanel';
import SiteSearch from '@/components/layout/SiteSearch';
export default{
	components: {
    SiteSearch,
    UserPanel
  },
  computed: {
    ...mapGetters('config', ['siteName', 'siteLogo', 'siteLogoSquare']),
  },
  methods: {
    ...mapMutations(['TOGGLE_SIDEBAR'])
  },
	data() {
		return {
			search: '',
		}
	},
}
</script>
<style lang="scss">
.top-bar{
	padding: .5em;
	height: 2.5em;
	position: fixed;
	top: 0;
	left: 0;
	width: calc(100% - 1em);
	display: flex;
	align-items: center;
	justify-content: space-between;
  z-index: 10000000;
  border-bottom: 1px solid var(--input-border-color);
  background: var(--sidebar-color);
	&__left {
		display: flex;
		align-items: center;
	}
  &__center {
    flex: 1;
    margin: 0 5em;
    @media screen and (max-width: 1366px) {
      margin: 0;
    }
  }
}
.site-logo {
	margin: 0 1.25em;
	&__picture{
		height: 2.5em;
    display: none;
    &--big {
      opacity: .875;
      transition: all .25s;
      display: block;
      &:hover {
        opacity: 1;
      }
    }
	}
}
@media screen and (max-width: 768px) {
  .site-logo{
    margin: 0;
  }
  .top-bar{
    width: 100%;

    &__center {
      display: none;
    }
    &__menu-toggle{
      margin: 0 1em 0 0!important;
    }
  }
}
</style>
