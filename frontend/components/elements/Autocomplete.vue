<template>
<div class="autocomplete" v-click-outside="hideAutocomplete">
	<div class="autocomplete__input" ref="input">
		<m-input :errors="errors" @click="visible = true" @keyup="onInputChange" :placeholder="placeholder" v-model="val" />
		<i class="autocomplete__input__loading-icon" v-show="isLoading"><m-preloader stroke-width="5" circle-width="20" /></i>
		<div :style="{'width': variantsPosition.width+'px', 'left': variantsPosition.x+'px', 'top': variantsPosition.y+'px'}" class="autocomplete__variants" v-show="visible">
			<div :key="$index" v-for="(variant,$index) in variants" class="autocomplete__variant" @click="setVariant(variant)">
        {{variant.name}}
      </div>
		</div>
	</div>

</div>
</template>
<style lang="scss">
.autocomplete {
	position: relative;
	flex: 1;
	&__input {
		position: relative;
		&__loading-icon {
			position: absolute;
			top: 6px;
			right: 6px;
		}
	}
	&__variants {
		position: fixed;
		z-index: 10000;
		background: rgba(0, 0, 0, .75);
    margin-top: 2em;
		width: 100%;
		max-height: 25em;
		overflow-x: hidden;
		overflow-y: auto;
    .theme-default & {
      box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.35);
    }
	}

	&__variant {
		transition: all .35s;
		cursor: pointer;
		padding: .5em 1em;
		&:hover {
			background: rgba(255, 255, 255, 0.15);
		}
	}
}
</style>
<script>
import clickOutside from 'vue-click-outside';
export default {
	directives:{
	  clickOutside
  },
	props: {
		errors: {
			type: Array,
			required: false,
			default: ()=>{
				return []
			}
		},
		fn: {
		  type: Function,
      required: true
    },
		value: {
			type: String,
			required: true,
		},
		placeholder:{
			type: String,
			required: false,
		}
	},
	data() {
		return {
			visible: false,
			variants: [],
			isLoading: false,
			val: '',
      variantsPosition: {
			  width: 0,
        x: 0,
        y: 0,
      },
      timeout: null
		}
	},
	watch:{
	  visible(isVisible) {
	    if (isVisible) {
        this.setPosition();
      }
    },
		value(newVal) {
			this.val = newVal;
		},
		val(newVal) {
			if (!this.returnVariant) {
				this.$emit('input',newVal);
			}
		},
	},
	methods: {
    setPosition() {
      let input = this.$refs.input;
      let rect = input.getBoundingClientRect();
      this.variantsPosition.width = rect.width;
      this.variantsPosition.x = rect.left;
      this.variantsPosition.y = rect.top;
    },
    setVariant(variant) {
      this.val = variant.id;
      this.visible = false;
    },
    hideAutocomplete() {
      this.visible = false;
    },
    onInputChange() {
      clearTimeout(this.timeout);
      this.timeout = setTimeout(() => {
        if (!this.isLoading) {
          this.isLoading = true;
          this.fn(this.val).then(res => {
            this.visible = true;
            this.isLoading = false;
            this.variants = res;
          })
        }
      }, 250)
    }
  }
}
</script>
