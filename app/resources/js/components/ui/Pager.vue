<template>
  <div class="pager" v-show="pagesCount > 1" :class="{'pager-vertical': vertical}">
    <a class="pager__link" :key="$index" @click="goToPage(page)" :class="getPageClasses(page)"
       v-for="(page, $index) in pager">
      <c-icon class="pager__link__icon" v-if="page.text === '<'" :icon="vertical? 'fa-chevron-up' : 'fa-chevron-left'"/>
      <c-icon class="pager__link__icon" v-else-if="page.text === '>'"
              :icon="vertical ? 'fa-chevron-down' : 'fa-chevron-right'"/>
      <span v-else class="pager__link__text">{{ page.text }}</span>
    </a>
  </div>
</template>
<script lang="ts" setup>
const props = withDefaults(defineProps<{
  vertical?: boolean,
  pagesCount: number,
  maxItems: number
}>(), {
  maxItems: 4
})

const emit = defineEmits<{
  (e: 'pageChange', n: number): void
}>();

const currentPage = defineModel<number>();

interface PagerItem {
  value: number;
  text: string,
  selectable: boolean
}

const pager = ref<PagerItem[]>([]);


const goToPage = (page: PagerItem) => {
  if (page.selectable) {
    currentPage.value = page.value;
    emit('pageChange', page.value);
  }
}

const generatePager = () => {
  pager.value = [];

  let pagerArray: PagerItem[] = [{
    value: 1,
    text: `1`,
    selectable: true
  }];

  let pageNumbers: number[] = [1];

  const start = currentPage.value - props.maxItems;
  const end = currentPage.value + props.maxItems;

  if (start > 2) {
    pagerArray.push({
      value: null,
      text: '...',
      selectable: false
    });
  }
  for (let i = start; i <= end; i++) {
    if (i >= 1 && i <= props.pagesCount && pageNumbers.indexOf(i) === -1) {
      pageNumbers.push(i);
      pagerArray.push({
        value: i,
        text: `${i}`,
        selectable: true
      });
    }
  }

  if (pageNumbers.indexOf(props.pagesCount) === -1) {
    if (props.pagesCount - end > 1) {
      pagerArray.push({
        value: null,
        text: '...',
        selectable: false
      })
    }
    pagerArray.push({
      value: props.pagesCount,
      text: `${props.pagesCount}`,
      selectable: true
    });
  }

  pagerArray.unshift(
      {
        value: currentPage - 1,
        text: '<',
        selectable: currentPage > 0
      }
  );
  pagerArray.push(
      {
        value: currentPage + 1,
        text: '>',
        selectable: currentPage < props.pagesCount
      }
  );
  pager.value = pagerArray;
}

watch(props.pagesCount, generatePager);
watch(currentPage, generatePager);
generatePager();

const getPageClasses = (page: PagerItem) => {
  return {
    'pager__link--active': currentPage.value === page.value,
    'pager__link--unselectable': !page.selectable
  }
}


</script>
<style lang="scss">
.pager-vertical-container {
  position: fixed;
  top: 0;
  height: 100%;
  right: 0;
  width: 5em;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pager {
  display: flex;
  align-items: center;
  background: var(--darken-5);
  padding: .5em;
  border-radius: var(--border-radius);

  &-vertical {
    flex-direction: column;
  }

  &__link {
    color: var(--text-color);
    opacity: .7;
    padding: 0 .5em;
    cursor: pointer;
    transition: opacity .25s;
    line-height: 1;

    &:hover {
      opacity: .875;
    }

    &--active {
      opacity: 1;
    }

    &--unselectable {
      opacity: .2;
      cursor: default;
    }

    &__icon {
      font-size: .75em;
    }

    &__text {
      font-size: 1.325em;
    }
  }

  &-vertical &__link {
    margin: .25em 0;
  }

  &--bottom-left {
    position: fixed;
    left: 1em;
    bottom: 1em;
  }

  &--bottom-right {
    position: fixed;
    right: 1em;
    bottom: 1em;
  }
}

@media screen and (max-width: 768px) {
  .pager {
    justify-content: flex-start;

    &-vertical {
      justify-content: space-between;
    }

    &-vertical-container {
      position: fixed;
      right: 0;
      width: 3.5em;
      z-index: 100000000;
      opacity: .65;

      &--mobile-default {
        position: inherit;
        width: auto;
        margin: 1em 0;
      }
    }

    &__link {
      font-size: .875em;

      &__inner {
        padding: .75em 1em;
      }
    }
  }
}

</style>
