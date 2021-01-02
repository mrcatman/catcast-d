<template>
<div class="input__container"  :class="containerClasses">

	<div class="input__title" @click="onClick" :class="{'input__title--with-icon':icon, 'input__title--inline':inlinePlaceholder}" v-show="((inlinePlaceholder && val.length === 0) || !inlinePlaceholder) && ((val && val.length === 0) || !this.hidePlaceholder)" v-if="title || placeholder"><span v-if="placeholder">{{placeholder}}</span><span v-else>{{title}}</span></div>

  <div class="input__inner">
		<span class="input__prepend" v-if="prepend">{{prepend}}</span>
		<slot></slot>
    <span class="input__append" v-if="append">{{append}}</span>
	</div>

	<div class="input__errors" v-show="errors && errors.length>0">
		<div v-for="(error,$index) in errors" :key="$index" class="input__error">{{printError(error)}}</div>
	</div>
  <div class="input__warnings" v-show="warnings && warnings.length > 0 && wasFocused">
    <div v-for="(warning,$index) in warnings" :key="$index" class="input__warning">{{$t(warning)}}</div>
  </div>

  <div class="input__description" v-if="description && description !== '' ">{{description}}</div>
</div>
</template>
<style lang="scss">
.input{
  &__icon {
    padding: .25em;
    color: var(--text-sub-color);
    z-index: 100;
    &--warning{
      left: auto;
      right: 0;
      color: var(--text-warning-color);
    }
  }
  &__loading {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent 25%, #ffffff4a, transparent 75%) no-repeat;
    z-index: 1;
    background-size: 350%;
    animation: inputLoading 3s linear infinite;
  }
	&__container{
    flex: 1;
		position: relative;
		margin: 1.5em 0;
    &--disabled {

    }
		&--single{
			margin: 1em 0;
		}
	}
  &__container--disabled &__inner {
    opacity: .5;
  }

  &__container--with-errors &__element-container {
    border: 1px solid var(--negative-color)!important;
    box-shadow: 0 0.25em 1.75em -0.75em var(--negative-color)!important;
  }

	&__prepend{
		border-top-left-radius: 5px;
		border-bottom-left-radius: 5px;
		font-weight: 500;
		display: block;
		font-size: .75em;
    padding: .95em;
    background: rgba(255,255,255,.1);
	}
  &__append {
    border-top-right-radius: 5px;
    border-bottom-right-radius: 5px;
    font-weight: 500;
    display: block;
    font-size: .75em;
    padding: .95em;
    background: rgba(255,255,255,.1);
  }
  &__element-container--with-append &__element-container {
    border-top-right-radius: 0!important;
    border-bottom-right-radius: 0!important;
  }

  &__element-container--with-prepend &__element-container {
    border-top-left-radius: 0!important;
    border-bottom-left-radius: 0!important;
  }
	&__inner{
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
	&__description{
    opacity: .875;
    font-size: .75em;
    padding: .5em 0 1em;
	}
	&__errors {
    text-align: left;
    font-size:.75em;
		color: var(--red);
		font-weight: 500;
		padding:.25em 0;
	}
  &__warnings {
    font-size: .75em;
    color: var(--text-warning-color);
    font-weight: 500;
    padding: .5em 0 0;
  }
	&__outer-title {
		font-size: 1em;
		color: #ddd;
		margin: 1em 0;
	}
	&__title{
    white-space: nowrap;
		position: absolute;
    z-index: 1000;
    top: .25em;
    left: .5em;
    font-size: 1em;
    font-weight: 400;
		line-height: 2em;
		transition: all 0.25s;

		&--with-icon {
      left:2.5em;
    }
		color: var(--text-sub-color);
    &--inline {
      top: .1em!important;
      left: .5em!important;
      font-size: 1em!important;
    }
	}
	&__container-not-empty &__title{
		font-size:.75em;
		top:-2em;
		left:0;
	}


	&__element-container{
    width: 100%;
		background: none;
		border: none;
		position: relative;
		z-index: 1;
		outline: none!important;
		flex: 1;
    padding: .5em;
    background: rgba(255, 255, 255, .125);
    box-shadow: 0 .5em 1em -0.25em rgba(255, 255, 255, .125);
    border-radius: .25em;
    &--with-warning {
      border-bottom-color: var(--text-warning-color);
    }
    &--disabled {
       color: var(--inactive-text-color);
    }
    &__right {
      position: absolute;
      top: 0;
      right: 0;
      height: 100%;
      display: flex;
      color: var(--text-sub-color);
      z-index: 100;
    }
	}


  &__element {
    width: 100%;
    outline: none!important;
    background: none;
    border: 0;
    color: var(--input-text-color);
    font-family: inherit;
    font-size: inherit;
    &--with-icon {
      margin: 0 0 0 1.75em;
    }
    &--one-line {
      display: flex;
    }
  }
	&__container-not-empty &__element-container{
			border-color: var(--input-border-active-color);
	}

  &__slider {
    position: absolute;
    left: 0;
    bottom: -.5em;
    width: calc(100% - .5em);
    height: 1em;
    cursor: pointer;
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
    background: rgba(6, 25, 39, 0.75);
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
  &__show-password {
    font-size: .875em;
    margin: .75em;
    display: inline-block;
    padding: 0 0 1.25em;
    cursor: pointer;
    border-bottom: 1px solid;
  }
}
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
input:-webkit-autofill:active  {
  -webkit-text-fill-color: #ccd8e1;
  -webkit-box-shadow: 0 0 0 30px #293741 inset !important;
}
  @keyframes inputLoading {
    0% {
      background-position: 0 0;
    }
    100% {
      background-position: 110% 0;
    }
  }
</style>
<script>
export default {
	props:{
	  disabled: {
	    type: Boolean,
      required: false,
    },
	  data: {
	    type: Boolean,
      required: false,
    },
    autoSize: {
      type: Boolean,
      required: false,
    },
    loading: {
      type: [Boolean],
      required: false,
      default: false,
    },
    inlinePlaceholder: {
      type: [Boolean],
      required: false,
    },
    wasFocused:{
      type: [Boolean],
      required: false,
    },
    icon:{
      type: [String],
      required: false,
      default: "",
    },
    hidePlaceholder:{
        type: Boolean,
        required: false,
        default: false,
    },
		single:{
			type:[Boolean],
			required:false,
		},
		tags:{

		},
		description:{
			type:String,
			required:false,
		},
    append:{
      type:String,
      required:false,
    },
		prepend:{
			type:String,
			required:false,
		},
    warnings:{
      type: Array,
      required: false,
    },
		errors:{
			type:Array,
			required:false
		},
		placeholder:{
			type:String,
			required:false,
		},
		title:{
			type: [String, Number],
			required: false,
		},
		inputValue:{
			required: true,
		},
		type:{
			type: String,
			required: false,
			default: 'text',
		},
    noErrors: {
      type: [Boolean],
      required: false,
    }
	},
  computed: {
	  containerClasses() {
      return{
        'input__container--with-errors': this.errors && this.errors.length > 0,
        'input__container-with-warnings': this.warnings && this.warnings.length > 0,
        'input__container--disabled': this.disabled,
        'input__container--single': this.single,
        'input__container-not-empty': (this.val !== ''),
        'input__container--without-errors': this.noErrors,
        'input__element-container--auto-size': this.autoSize,
        'input__element-container--with-prepend': this.prepend,
        'input__element-container--with-append': this.append
      }
    }
  },
	watch:{
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
	methods:{
    onClick(e) {
	    this.$emit('click', e);
    },
	  printError(error) {
	    if (Array.isArray(error)) {
	      return this.$tc(error[0], error[1]);
      } else {
	      return this.$t(error);
      }
    },
		onKeyup(e) {
			this.$emit('keyup',e);
		},
		onChange(e) {
			this.$emit('change',e);
		}
	}
}
</script>
