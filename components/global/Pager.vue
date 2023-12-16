<template>
<div class="pager" v-show="pagesCount > 1" :class="{'pager-vertical': vertical}">
	<a class="pager__link" :key="$index" @click="goToPage(page)" :class="getPageClasses(page)" v-for="(page, $index) in pager">
		<c-icon class="pager__link__icon" v-if="page.text === '<'" :icon="vertical? 'fa-chevron-up' : 'fa-chevron-left'" />
    <c-icon class="pager__link__icon" v-else-if="page.text === '>'" :icon="vertical ? 'fa-chevron-down' : 'fa-chevron-right'" />
    <span v-else class="pager__link__text">{{page.text}}</span>
	</a>
</div>
</template>
<script>
export default {
  props: {
    vertical: Boolean,
    value: Number,
    data: Object,
    count: {
      type: Number,
      required: false,
    },
  },
  data() {
    return {
      maxItems: 3,
      pager: [],
      currentPage: this.value ? this.value : 1,
      pagesCount: (this.count !== undefined ? this.count : (this.data.last_page ? this.data.last_page : Math.ceil(this.data.count / this.data.on_page))),
    }
  },
  watch: {
    count(newCount) {
      this.pagesCount = newCount;
      this.generatePager();
    },
    data(newData) {
      this.pagesCount = (newData.last_page ? newData.last_page : Math.ceil(newData.count / newData.on_page));
      this.generatePager();
    },
    currentPage(newVal) {
      this.$emit('input', newVal);
      this.generatePager();
    },
    value(newVal) {
      this.currentPage = newVal;
    }
  },
  mounted() {
    this.generatePager();
  },
  methods: {
    goToPage(page) {
      if (page.selectable) {
        this.currentPage = page.value;
        this.$emit('pageChange', page.value);
      }
    },
    generatePager() {
      this.pager = [];
      let pagesCount = this.pagesCount;
      let pagerArray = [1];
      let currentPage = this.currentPage;
      let start = currentPage - this.maxItems;
      let end = currentPage + this.maxItems;

      if (start > 2) {
        pagerArray.push('...');
      }
      for (let i = start; i <= end; i++) {
        if (i >= 1 && i <= pagesCount && pagerArray.indexOf(i) === -1) {
          pagerArray.push(i);
        }
      }

      if (pagerArray.indexOf(pagesCount) === -1) {
        if (pagesCount - end > 1) {
          pagerArray.push('...');
        }
        pagerArray.push(pagesCount);
      }

      let pager = pagerArray.map(item => {
        return {
          value: item,
          text: item,
          selectable: item > 0
        };
      });
      pager.unshift(
        {
          value: currentPage - 1,
          text: '<',
          selectable: currentPage > 0
        }
      );
      pager.push(
        {
          value: currentPage + 1,
          text: '>',
          selectable: currentPage < pagesCount
        }
      );
      this.pager = pager;
    },
    getPageClasses(page) {
      return {
        'pager__link--active': this.currentPage === page.value,
        'pager__link--unselectable': !page.selectable
      }
    }
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
  border-radius: .25em;
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
