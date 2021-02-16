<template>
<div class="tabs" :class="{'tabs--small':small, 'tabs--toggle': toggle, 'tabs--full-width': fullWidth}">
  <div class="tabs__inner" :class="{'tabs__inner--vertical': vertical}" ref="inner" :style="{width: width+'px'}">
     <a @click="setTab(tab)" :key="$index" v-for="(tab, $index) in list" class="tabs__item" :class="{'tabs__item--current':currentTab === tab.id}" >
       <span class="tabs__item__text">{{tab.name}}</span>
    </a>
  </div>
</div>
</template>
<style lang="scss">
.tabs {
  width: 100%;
  position: relative;
  border-bottom: 2px solid rgba(255, 255, 255, .1);
  @media screen and (max-width: 768px) {
    overflow: auto;
  }

  &__inner {
    display: inline-flex;
    justify-content: space-around;
    position: relative;
    min-height: 2.5em;
    margin: 0 0 -2px;
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
    margin: 0;
    position: relative;
    z-index: 1;
    transition: all .25s;
    cursor: pointer;
    @media screen and (max-width: 768px) {
      padding: .5em 1em;
    }

    &__text {
      white-space: nowrap;
    }

    &:hover {
      background: rgba(255, 255, 255, 0.05);
    }

    &--current {
      background: rgba(255, 255, 255, 0.1);
      border-bottom: 2px solid var(--active-color);
    }
  }

  &--small &__item {
    font-size: 1em;
  }

  &--full-width &__inner {
    display: flex;
    justify-content: center;
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
    border-radius: .5em;
    font-size: 1em;
    border-bottom: 0;
  }

  &--toggle &__item--current {
    background: rgba(255, 255, 255, 0.05);
  }

  &__inner--vertical {
    width: 100%;
    flex-direction: column;
    text-align: left;
  }

  &__inner--vertical &__item {
    text-align: left;
    border-bottom: 0 solid rgba(255, 255, 255, 0);
    border-left: 2px solid rgba(255, 255, 255, 0);

    &--current {
      background: rgba(255, 255, 255, 0.1);
      border-bottom: 0 solid rgba(255, 255, 255, 0);
      border-left: 2px solid var(--active-color);
    }
  }
}
@media screen and (max-width: 768px) {
  .list-container .tabs {
    overflow: visible;
  }
}
</style>
<script lang="ts">
  import Vue, {PropType} from 'vue'
  import TabData from '~/components/types/Tabs'
  import { Route } from "vue-router"

  export default Vue.extend({
	props: {
    list:{
      type: Array as PropType<Array<TabData>>,
      required: true,
    },
    value:{
      type: String,
      required: true
    },


	  remember: {
	    type: Boolean,
      required: false,
      default: false
    },
	  vertical: {
	    type: Boolean,
      required: false
    },
	  namespace: {
	    type: String,
      required: false,
      default: 't',
    },
	  toggle: {
	    type: Boolean,
      required: false,
    },
	  small:{
	    type: Boolean,
      required: false,
    },
    fullWidth: {
      type: Boolean,
      required: false,
    }
	},
	watch:{
		currentTab(tab) {
			this.$emit('input',tab);
		}
	},
	data() {
    return {
      width: null,
      currentTab: this.value,
    }
  },
  mounted(): void {
	  if (this.remember) {
      if (this.$route.query[this.namespace]) {
        let tab = this.list.filter(tab => tab.id === this.$route.query[this.namespace]);
        if (tab[0]) {
          this.currentTab = tab[0].id;
        }
      }
    }
  },
	methods:{
		setTab(tab: TabData): void {
      this.currentTab = tab.id;
      if (this.remember) {
        let query: any = {};
        Object.keys(this.$route.query).forEach(key => {
          query[key] = this.$route.query[key];
        });
        query[this.namespace] = tab.id;
        this.$router.push({ query })
      }
		}
	}
})
</script>
