<template>
<div class="pager"  :class="{'pager--vertical': vertical}">
	<a class="pager__link" :key="$index" @click="goToPage(page)" :class="{
	  'pager__link--active': currentPage === page.value,
    'pager__link--unselectable': !page.selectable
    }" v-for="(page,$index) in pager">
		<i v-if="page.text === '<'" class="fa fa-chevron-left"></i>
    <i v-else-if="page.text == '>'" class="fa fa-chevron-right"></i>
    <span v-else>{{page.text}}</span>
	</a>
</div>
</template>
<script>

export default{
	props:{
		vertical: Boolean,
		value: Number,
		data: Object,
	},
  computed: {
    pagesCount() {
      return this.data.pagesCount;
    }
  },
	data() {
		return {
			itemsShownCount: 3,
			pager: [],
			currentPage: this.value ? this.value : 1,
		}
	},
	watch: {
		data(newData) {
			this.generatePager();
		},
		currentPage(newVal, oldVal){
			this.$emit('input',newVal);
      this.generatePager();
		},
		value(newVal,oldVal) {
			this.currentPage = newVal;
		}
	},
	mounted() {
		this.generatePager();
	},
	methods:{
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
			let start = currentPage - this.itemsShownCount;
			let end = currentPage + this.itemsShownCount;

			if (start > 2) {
				pagerArray.push('...');
			}
			if (pagesCount > 0) {
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
      }
			let pager = pagerArray.map(item=>{
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
          selectable: currentPage - 1 > 0
			  }
      );
			pager.push(
			  {
          value: currentPage+1,
          text: '>',
          selectable: currentPage < pagesCount
			  }
      );
			this.pager = pager;
		},
		countPages() {

		}
	}
}
</script>
<style lang="scss">


.pager{
	display: flex;
	&--vertical{
		flex-direction: column;
	}
	&__link {
    background: rgba(255, 255, 255, .1);
		display: block;
		margin: 0 .5em 0 0;
    padding: .5em 1em;
		cursor: pointer;
    text-align: center;
    transition: background-color .25s;
    &:hover {
      background: rgba(255, 255, 255, .2);
    }
		&--active {
      cursor: default;
			background: var(--active-color);
			color: var(--text-color);
      &:hover {
        background: var(--active-color);
      }
		}
		&--unselectable {
			opacity: .5;
      cursor: default;
      &:hover {
        background: rgba(255, 255, 255, .1);
      }
    }
    i {
      font-size: .75em;
    }
	}
	&-vertical &__link {
		margin: .25em 0;
	}
	&--bottom-left {
		position: absolute;
		left: 1em;
		bottom: 1em;
	}
	&--bottom-right{
		position:absolute;
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
