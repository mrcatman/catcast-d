<template>
<div class="colorpicker" ref="colorpicker" :class="{'colorpicker--small': small}">
 <div class="colorpicker__value" v-click-outside="hide" :style="{background:val}" @click="colorpickerVisible = true">
		<div class="colorpicker__wrapper" :style="{'left': position.x+'px', 'top': position.y+'px'}" v-show="colorpickerVisible">
			<chrome-picker class="colorpicker__element" @input="updateValue" :value="colorpickerVal" />
		</div>
	</div>
	<div class="colorpicker__title" v-if="title">{{title}}</div>
</div>
</template>
<style lang="scss">
.vc-chrome {
  &-toggle-icon path {
    fill: var(--input-texts-color);
  }

  &-fields .vc-input__input {
    background: var(--input-bg-color) !important;
    font: inherit !important;
    box-shadow: none !important;
    color: var(--input-texts-color) !important;
    font-size: .75em !important;
  }

  &-toggle-icon-highlight {
    background: var(--input-bg-color);
  }

  &-body {
    color: var(--text-color);
    background-color: var(--box-element-color) !important;
  }
  &-fields-wrap {
    display: none;
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
	margin: .5em;
  &__wrapper {
    position: fixed;
    z-index: 1000;
  }
	&__element {
    position: absolute;
    font-family: inherit !important;
    left: 0;
    bottom: 0;
	}
	&__value {
		cursor: pointer;
		width: 3.5em;
		height: 1.75em;
		border-radius: var(--border-radius);
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
    margin: 0 .5em;
  }
  &--small &__value {
    width: 1.75em;
  }
}
</style>
<script>
import { Chrome } from 'vue-color'
import clickOutside from 'vue-click-outside';

export default {
	directives: {
	  clickOutside
  },
	components: {
		'chrome-picker': Chrome,
	},
	props: {
		errors: {
			type: Array,
			required: false,
			default: ()=>{
				return []
			}
		},
    small: Boolean,
		value: {
			type: String,
			required: false,
      default: '#fff',
		},
		title: String,
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
	watch: {
		colorpickerVal(newVal) {
			this.val = newVal.hex;
		},
		value(newVal) {
			this.val = newVal;
		},
		val(newVal) {
			this.$emit('input',newVal);
		},
    colorpickerVisible(isVisible) {
		  if (isVisible) {
		    const rect = this.$refs.colorpicker.getBoundingClientRect();
		    this.position.x = rect.left + rect.width;
		    this.position.y = rect.top;
      }
    }
	},
	methods: {
		hide() {
			this.colorpickerVisible = false;
		},
		updateValue(newVal) {
			this.val = newVal.hex;
		},
	}
}
</script>
