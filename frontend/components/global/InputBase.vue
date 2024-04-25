<template>
<div class="input__container" :class="containerClasses">
  <div v-if="disabled" class="input__disabled-overlay"></div>
  <div class="input__loading-overlay" v-if="loading">
    <c-preloader />
  </div>

	<c-input-title v-if="title">{{title}}</c-input-title>

	<div class="input__inner">
		<span class="input__prepend" v-if="prepend">{{prepend}}</span>
		<slot></slot>
    <span class="input__append" v-if="append">{{append}}</span>
	</div>
	<div v-if="!errors || errors.length === 0 && description && description !== ''" class="input__description">{{description}}</div>
  <div class="input__errors" v-if="errors && errors.length > 0">
		<c-translated-message v-for="(error, $index) in errors" :message="error" :key="$index" class="input__error" />
	</div>
  <div class="input__warnings" v-if="warnings && warnings.length > 0">
    <div v-for="(warning,$index) in warnings" :key="$index" class="input__warning">{{$t(warning)}}</div>
  </div>
</div>
</template>
<style lang="scss">
.input {
  &__loading-overlay {
    position: absolute;
    right: .25em;
    top: .25em;
    height: calc(100% - .5em);
    .preloader-container {
      height: 100%;
      padding: 0;
    }
  }

  &__container {
    flex: 1;
    position: relative;
    padding: var(--vertical-margin) 0;
  }

  &__container--disabled &__inner {
    opacity: .5;
  }



  &__prepend {
    border-top-left-radius: .5em;
    border-bottom-left-radius: .5em;
    font-weight: 500;
    display: block;
    font-size: .75em;
    padding: 0 1em;
    height: 3.125em;
    line-height: 3.125;
    background: var(--lighten-2);
    .theme-default & {
      background: #525252;
      color: #eee;
      box-shadow: 0 5px 15px -5px rgba(0, 0, 0, 0.75);
    }
  }

  &__append {
    border-top-right-radius: .5em;
    border-bottom-right-radius: .5em;
    font-weight: 500;
    display: block;
    font-size: .75em;
    padding: 0 1em;
    height: 3.125em;
    line-height: 3.125;
    background: var(--lighten-2);

  }

  &__element-container--with-append &__element-container {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
  }

  &__element-container--with-prepend &__element-container {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
  }

  &__inner {
    position: relative;
    display: flex;
    align-items: center;
  }

  &__disabled-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
  }

  &__description {
    color: var(--input-descriptions-color);
    font-size: .875em;
    margin: .5em 0 1em;
  }

  &__errors {
    text-align: left;
    font-size: .75em;
    color: var(--red);
    font-weight: 500;
    padding: .25em 0;
  }

  &__warnings {
    font-size: .75em;
    color: var(--text-warning-color);
    font-weight: 500;
    padding: .75em 0 0;
  }

  &__element-container {
    width: 100%;
    background: none;
    border: none;
    position: relative;
    z-index: 1;
    outline: none !important;
    flex: 1;
    display: flex;
    background: var(--input-bg-color);
    border-color: var(--input-border-color);
    height: 2.40625em;
    border-radius: var(--border-radius);
    &--with-warning {
      border-bottom-color: var(--text-warning-color);
    }
    &--disabled {
      color: var(--inactive-text-color);
    }
  }

  &__container--with-errors &__element-container {
    border: 1px solid var(--negative-color) !important;
  }

  &__element {
    width: 100%;
    outline: none !important;
    background: none;
    border: 0;
    color: var(--input-texts-color);
    font-family: inherit;
    font-size: inherit;
    padding: .5em;
    height: 100%;
    box-sizing: border-box;
    border-radius: var(--border-radius);
  }

  &__container-not-empty &__element-container {
    border-color: var(--input-border-active-color);
  }

  &__icons {
    display: flex;
    align-items: center;
    color: var(--input-titles-color);
    padding: 0 .5em;
    &:empty {
      display: none;
    }
  }
  &__show-password-icon {
    cursor: pointer;
    opacity: .5;
    &:hover {
      opacity: .75;
    }
    &--active {
      opacity: 1;
      text-shadow: 0 0 .25em var(--active-color), 0 0 .75em var(--active-color);
    }
  }

  &__warning-icon {
    color: var(--text-warning-color);
  }

  &__dropdowns {
    z-index: 10000;
    position: absolute;
    left: 0;
    width: 100%;
    top: 2.5em;
    display: flex;
  }

  &__dropdown {
    flex: 1;
    display: flex;
    flex-direction: column;
    max-height: 10em;
    overflow: auto;

    &__variant {
      text-align: center;
      cursor: pointer;

      &:hover {
        background: rgba(255, 255, 255, .1);
      }

      &--active {
        background: var(--active-color);

        &:hover {
          background: var(--active-color);
        }
      }
    }
  }

  .theme-modern &__dropdown {
    background: rgba(6, 25, 39, 0.75);
  }
}
div.input__element {
  display: flex;
}
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
input:-webkit-autofill:active  {
  -webkit-text-fill-color: #ccd8e1;
  -webkit-box-shadow: 0 0 0 30px #293741 inset !important;
}
</style>
<script>
export default {
  computed: {
    containerClasses() {
      return {
        'input__container--with-errors': this.errors && this.errors.length > 0,
        'input__container--disabled': this.disabled,
        'input__container-not-empty': (this.val !== '' || (this.append && this.append !== '') || (this.prepend && this.prepend !== '')),
        'input__element-container--with-prepend': this.prepend,
        'input__element-container--with-append': this.append
      }
    }
  },
	props: {
	  disabled: Boolean,
	  loading: Boolean,
    wasFocused: Boolean,
    icon: String,
		description: String,
    append: String,
		prepend: String,
    warnings: Array,
		errors: Array,
		title: [String, Number],
		inputValue: {
			required: true,
		},
		type: {
			type: String,
			required: false,
			default: 'text',
		},
	},
	watch: {
		inputValue(newVal) {
			this.val = newVal;
		},
		val(newVal) {
			this.$emit('input',newVal);
		}
	},
	data() {
		return {
			val:this.inputValue,
		}
	},
	methods: {
		onKeyup(e) {
			this.$emit('keyup',e);
		},
		onChange(e) {
			this.$emit('change',e);
		}
	}
}
</script>
