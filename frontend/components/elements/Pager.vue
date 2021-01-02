<template>
<div class="pager" v-show="pagesCount > 1" :class="{'pager-vertical': vertical}">
	<a class="pager__link" :key="$index" @click="goPage(page)" :class="{'pager__link--active':currentPage === page.value,'pager__link-unselectable':!page.selectable}" v-for="(page,$index) in pager">
		<touch-ripple class="pager__link__inner" :speed="1" :opacity="0.3" color="#fff" transition="ease"><i v-if="page.text === '<'" class="fa fa-chevron-left"></i><i v-else-if="page.text == '>'" class="fa fa-chevron-right"></i><span v-else>{{page.text}}</span></touch-ripple>
	</a>
</div>
</template>
<script>
import isMobile from '~/functions/isMobile.js';
export default{
	props:{
		vertical: Boolean,
		value: Number,
		data: Object,
		count:{
			type: Number,
			required: false,
		},
	},
	data() {
		return {
			itemsShownCount: isMobile() ? 2 : 3,
			pager: [],
			currentPage: this.value ? this.value : 1,
			pagesCount: (this.count !== undefined ? this.count : (this.data.last_page ? this.data.last_page : Math.ceil(this.data.count/this.data.on_page))),
		}
	},
	watch:{
    count(newCount) {
      this.pagesCount = newCount;
      this.generatePager();
    },
		data(newData) {
			this.pagesCount = (newData.last_page ? newData.last_page : Math.ceil(newData.count/newData.on_page));
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
		goPage(page) {
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
			let start = currentPage-this.itemsShownCount;
			let end = currentPage+this.itemsShownCount;

			if (start>2) {
				pagerArray.push('...');
			}
			for (let i = start; i<=end; i++) {
				if (i >=1  && i <= pagesCount && pagerArray.indexOf(i) === -1) {
					pagerArray.push(i);
				}
			}

			if (pagerArray.indexOf(pagesCount) === -1) {
				if (pagesCount-end>1) {
					pagerArray.push('...');
				}
				pagerArray.push(pagesCount);
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
          value: currentPage-1,
          text: '<',
          selectable: currentPage > 0
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
.pager-vertical-container {
    position: fixed;
    top: 0;
    height: 100%;
    right: 1em;
    width: 5em;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pager{
	display: flex;
	&-vertical{
		flex-direction: column;
	}
	&__link {
		background: #eee;
		display: block;
		margin: 0 .5em 0 0;
		color: #333!important;
		font-weight: 500;
		border-radius: 3px;
		cursor: pointer;
    text-align: center;
		&--active {
			background: var(--active-color);
			color: var(--text-color)!important;
		}
		&-unselectable {
			color: #777;
			background: #ccc;
		}
		&__inner {
			padding: .5em 1em;
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
