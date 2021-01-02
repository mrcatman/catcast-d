<template>
<div class="colorpicker" ref="colorpicker" :class="{'colorpicker--small': small}">
  <div class="colorpicker__title colorpicker__title--left" v-if="titleLeft">{{titleLeft}}</div>
	<div class="colorpicker__value" v-click-outside="hideColorpicker" :style="{background:val}" @click="colorpickerVisible = true">
		<div class="colorpicker__element" :style="{'left': position.x+'px', 'top': position.y+'px'}" v-show="colorpickerVisible">
			<chrome-picker @input="updateColorpickerVal" :value="colorpickerVal" />
		</div>
	</div>
	<div class="colorpicker__title" v-if="title">{{title}}</div>
</div>
</template>
<style lang="scss">
.vc-chrome{
		position: absolute;
		font-family: inherit!important;
    right: 0;
    top: 2.25em;
		&-toggle-icon path {
			fill: var(--input-text-color);
		}

		&-fields .vc-input__input {
			background: var(--input-bg-color)!important;
			font: inherit!important;
			box-shadow: none!important;
			color: var(--input-text-color)!important;
			font-size: .75em!important;
		}

		&-toggle-icon-highlight {
			background: var(--input-bg-color);
		}

		&-body{
			color: var(--text-color);
			background-color: var(--box-element-color)!important;
		}

    &-alpha-wrap {
      display: none;
    }

    &-hue-wrap {
      margin-top: 10px;
    }
}
.colorpicker {
	display: flex;
	align-items: center;
	margin: .5em 0;
	&__element {
    position: fixed;
    z-index: 1000;
	}
	&__value {
		cursor: pointer;
		width: 3.5em;
		height: 1.75em;
		border-radius: .5em;
	}
	&__title {
		font-size: 1.25em;
    margin: 0 0 0 .5em;
    &--left {
      font-size: 1em;
      white-space: nowrap;
      margin: 0 .5em 0 0;
    }
	}
  &--small {
    margin: 0;
  }
  &--small &__value {
    margin: 0 0 0 .5em;
    width: 1.75em;
  }

}
</style>
<script>
import { Chrome } from 'vue-color'
import clickOutside from 'vue-click-outside';
export default {
	directives:{
	  clickOutside
  },
	components:{
		'chrome-picker': Chrome,
	},
	props:{
		errors:{
			type: [Array, Object],
			required: false,
			default: ()=>{
				return []
			}
		},
    small: {
		  type: [Boolean],
      required: false,
    },
		value:{
			type: [String, Object],
			required: false,
      default: '#fff',
		},
    titleLeft: {
		  type: [String],
      required: false,
    },
		title:{
			type: [String],
			required: false,
		},
	},
	data() {
		return {
			colorpickerVal: this.value,
			colorpickerVisible: false,
			val: this.value,
      position: {
			  x: 0,
        y: 0,
      }
		}
	},
	watch:{
		colorpickerVal(newVal) {
			this.val = newVal.hex;
			//this.$emit('input',newVal.hex);
		},
		value(newVal) {
			this.val = newVal;
		},
		val(newVal) {
			this.$emit('input',newVal);
		},
    colorpickerVisible(isVisible) {
		  if (isVisible) {
		    let colorpicker = this.$refs.colorpicker;
        let rect = colorpicker.getBoundingClientRect();
		    this.position.x = rect.left + rect.width;
		    this.position.y = rect.top;
      }
    }
	},
	created() {

	},
	methods:{
		hideColorpicker() {
			this.colorpickerVisible = false;
		},
		updateColorpickerVal(newVal) {
			this.val = newVal.hex;
		},
		openColorpicker() {
			this.colorpickerVisible = true;
		}
	}
}
</script>
