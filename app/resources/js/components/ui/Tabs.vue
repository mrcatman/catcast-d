<template>
  <div class="tabs" :class="{'tabs--small':small, 'tabs--vertical': vertical, 'tabs--toggle': toggle}">
    <div class="tabs__inner" ref="inner" :style="{width: containerWidth +'px'}">
      <component :is="tabsComponent(tab)" :to="tab.link" @click="setTab(tab)" :key="$index" v-for="(tab,$index) in data"
                 class="tabs__item" :class="{'tabs__item--active': currentTab === tab.id}">
        <c-icon class="tabs__item__icon" v-if="tab.icon" :icon="tab.icon"/>
        <span class="tabs__item__text">{{ tab.name }}</span>
      </component>
    </div>
  </div>
</template>
<script lang="ts" setup>
import isMobile from '@/helpers/isMobile';

const route = useRoute();
const router = useRouter();

export interface Tab {
  id: string;
  name: string;
  icon?: string;
  link?: string;
}

const props = withDefaults(defineProps<{
  data: Tab[];
  queryParam?: string,
  vertical?: boolean,

  toggle?: boolean,
  small?: boolean,
}>(), {
  queryParam: 't'
});

const currentTab = defineModel<string>();

const innerRef = useTemplateRef('inner');
const containerWidth = ref<number>(null);

const tabsComponent = ((tab: Tab) => {
  return tab.link ? resolveComponent('NuxtLink') : 'span';
})

const setTabByRoute = () => {
  const hasLinks = props.data.filter(tab => !!tab.link).length > 0;
  const val = hasLinks ? route.path : route.query[props.queryParam];
  const tab = hasLinks ? props.data.filter(tab => tab.link === val)[0] : props.data.filter(tab => tab.id === val)[0];
  if (tab) {
    currentTab.value = tab.id;
  }
}

const calcWidth = () => {
  if (isMobile()) {
    let width = 16;
    let children = [].slice.call(innerRef.value.children);
    children.forEach(item => {
      width += item.offsetWidth;
    });
    containerWidth.value = width;
  }
}


onMounted(() => {
  setTabByRoute();
  calcWidth();
})
watch(route, setTabByRoute);


const setTab = (tab: Tab) => {
  if (tab.link) {
    return;
  }
  currentTab.value = tab.id;
  let query = {};
  Object.keys(route.query).forEach(key => {
    query[key] = route.query[key];
  });
  query[props.queryParam] = tab.id;
  router.push({query})
}


</script>
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
      background: var(--active-color) !important;
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
