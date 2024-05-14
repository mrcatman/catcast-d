<template>
<div class="tabs" :class="{'tabs--small':small, 'tabs--vertical': vertical, 'tabs--toggle': toggle}">
  <div class="tabs__inner" ref="inner" :style="{width: width+'px'}">
    <component :is="tab.link ? 'nuxt-link' : 'span'" :to="tab.link" @click="setTab(tab)" :key="$index" v-for="(tab,$index) in data" class="tabs__item" :class="{'tabs__item--active': currentTab === tab.id}" >
       <c-icon class="tabs__item__icon" v-if="tab.icon" :icon="tab.icon" />
       <span class="tabs__item__text">{{tab.name}}</span>
    </component>
  </div>
</div>
</template>
<style lang="scss">
.tabs {
  position: relative;
  border-bottom: 2px solid var(--lighten-2);
  @media screen and (max-width: 768px) {
    overflow: auto;
  }

  &__inner {
    display: inline-flex;
    justify-content: space-around;
    position: relative;
    min-height: 2.5em;
    @media screen and (max-width: 768px) {
      padding-bottom: 2px;
    }
  }

  &:after {
    content: "";
    display: none;
    position: absolute;
    left: 0;
    width: 100%;
    height: 2px;
    background: rgba(255, 255, 255, 0.25);
    bottom: 0;
  }

  &__item {
    padding: .875em 1.25em;
    font-weight: 400;
    border-bottom: 2px solid rgba(255, 255, 255, 0);
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 1;
    transition: all .25s;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: -2px;
    &:hover {
      background: var(--lighten-1);
      border-bottom-color: var(--lighten-2);
    }
    &--active {
      background: var(--active-color)!important;
      border-bottom-color: var(--lighten-3);
    }
    @media screen and (max-width: 768px) {
      padding: .5em 1em;
    }
    &__icon {
      width: 2.25rem;
      margin-right: .25em;
    }
    &__text {
      white-space: nowrap;
    }
  }

  &--small &__item {
    font-size: .9375em;
  }

  &--toggle {
    width: auto;

    &:after {
      background: none;
    }
  }

  &--toggle &__item {
    min-height: auto;
  }

  &--toggle &__item {
    line-height: 1.75;
    padding: .5em .75em;
    border-radius: var(--border-radius);
    font-size: 1em;
    border-bottom: 0 !important;
  }

  &--toggle &__item--active {
    background: rgba(255, 255, 255, 0.05);
  }

  &--vertical {
    border-bottom-width: 0;
  }

  &--vertical &__inner {
    width: 100%;
    flex-direction: column;
    text-align: left;
  }

  &--vertical &__item {
    border-bottom-width: 0;
    border-left: .125em solid transparent;
    justify-content: flex-start;
    padding: 1.125em 1em;
    &:hover {
      border-left-color: var(--lighten-2);
    }
    &--active {
      border-left-color: var(--lighten-3);
    }
  }
}
.box__header .tabs {
  margin: -1em;
  font-size: .875em;
}

@media screen and (max-width: 768px) {
  .list-container .tabs {
    overflow: visible;
  }
}
</style>
<script>
import isMobile from '@/helpers/isMobile.js';
export default {
	props: {
	  vertical: Boolean,
	  namespace: {
	    type: String,
      required: false,
      default: 't',
    },
    noChangeHash: Boolean,
	  toggle: Boolean,
	  small: Boolean,
		data: {
			type: Array,
			required: true,
		},
		value: String,
	},
	watch: {
	  data() {
      this.calcWidth();
    },
		currentTab(tab) {
			this.$emit('input', tab);
		},
    $route() {
      this.setTabByRoute();
    }
	},
	data() {
    return {
      width: null,
      currentTab: this.value,
    }
  },
  mounted() {
    this.setTabByRoute();
    this.calcWidth();
  },
	methods: {
    setTabByRoute() {
      const hasLinks = this.data.filter(tab => !!tab.link).length > 0;
      const val = hasLinks ? this.$route.path : this.$route.query[this.namespace];
      const tab = hasLinks ? this.data.filter(tab => tab.link === val)[0] : this.data.filter(tab => tab.id === val)[0];
      if (tab) {
        this.currentTab = tab.id;
      }
    },
	  calcWidth() {
	    if (isMobile()) {
        let width = 16;
        let children = [].slice.call(this.$refs.inner.children);
        children.forEach(item => {
          width += item.offsetWidth;
        });
        this.width = width;
      }
    },
		setTab(tab) {
			this.currentTab = tab.id;
			let query = {};
			Object.keys(this.$route.query).forEach(key => {
			  query[key] = this.$route.query[key];
      });
			query[this.namespace] = tab.id;
			//console.log(query);
			if (!this.noChangeHash) {
        this.$router.push({query})
      }
		}
	}
}
</script>
