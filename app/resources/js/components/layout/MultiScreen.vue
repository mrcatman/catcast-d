<template>
<div class="page-container">
  <c-preloader block  v-if="loading" />
	<div class="multi-screen">
		<div class="multi-screen__inner" :style="{width: 100*count+'%', left: ((this.currentPage-1)*(-100))+'%'}">
			<slot></slot>
		</div>
	</div>
</div>
</template>
<style lang="scss">

</style>
<script>
export default {
	props: {
	  active: {
	    type: Boolean,
      required: false,
      default: true
    },
	  manual: {
	    type: [Boolean, Number],
      required: false,
    },
	  loading: {
	    type: [Boolean, Number],
      required: false,
    },
		count: {
			type:Number,
			required:true,
		},
		value: {
			type:Number,
			required:false,
			default: 1,
		}
	},
	created() {
		window.addEventListener('wheel',this.onWheel);
	},
	watch: {
    value(newValue) {
      this.currentPage = newValue;
    },
		currentPage(newValue) {
			this.$emit('input',newValue);
		},
	},
	data() {
		return {
			currentPage: this.value,
			isScrolling: false,
		}
	},
	methods: {
		onWheel(e) {
		  if (!this.active) {
		    return;
      }
			if (!this.isScrolling) {
			  if (!this.manual) {
          let changed = false;
          let dir = (e.deltaY > 0) ? 1 : -1;
          if (dir === -1 && this.currentPage > 1) {
            this.currentPage--;
            changed = true;
          } else {
            if (dir === 1 && this.currentPage < this.count) {
              this.currentPage++;
              changed = true;
            }
          }
          if (changed) {
            this.isScrolling = true;
            setTimeout(() => {
              this.isScrolling = false;
            }, 500)
          }
        }
			}
		}
	}
}
</script>
