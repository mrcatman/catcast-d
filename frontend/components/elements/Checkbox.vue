<template>
<div class="checkbox" :class="{'checkbox--disabled': disabled}">
	<div class="checkbox__inner" @click="onClick()">
		<div v-if="this.switch" class="checkbox__switch" :class="{'checkbox__switch--active':val}">
			<span class="checkbox__switch__handle"></span>
		</div>
		<div v-else class="checkbox__element" :class="{'checkbox__element--active':val}">
			<span class="checkbox__element__tick" v-show="val"><i class="fa fa-check"></i></span>
		</div>
		<div class="checkbox__title" v-if="title">{{title}}</div>
	</div>
	<div class="checkbox__description" v-if="description">{{description}}</div>
</div>
</template>
<style lang="scss" scoped>
.checkbox {
  cursor: pointer;
	margin:.5em 0;
  &--disabled {
    opacity: .5;
  }
  &--in-row{
    margin-right: .75em;
  }
	&__inner{
		display: flex;
		align-items: center;
	}
	&__description {
		font-size: .95em;
		margin: .5em 0 0;
		color: #eee;
	}
	&__element {
		width: 1em;
		height: 1em;
    background: rgba(255, 255, 255, .125);
		cursor: pointer;
		display: flex;
		align-items: center;
		justify-content: center;
		&--active {
			background: var(--active-color);
		}
	}
	&__title {
		font-size:1em;
		font-weight: 500;
		white-space: nowrap;
		margin:0 0 0 .5em;
	}
	&__switch {
		position: relative;
		width: 3em;
		background: #292929;
		height: 1em;
		margin: 0 1em 0 0;
		border-radius: 50px;
		&__handle {
			background: #999;
			width: 1em;
			height: 1em;
			display: inline-block;
			position: absolute;
			left: 0;
			top: -1px;
			transition: all .35s;
			border-radius: 50px;
      .theme-default & {
        box-shadow: 0 5px 7px -1px rgba(0, 0, 0, 1);
      }
		}
	}
	&__switch--active &__switch__handle {
		background: var(--active-color);
		left: 2em;
	}
  .theme-flat &__switch {
    width: 2em;
  }
  .theme-flat &__switch--active &__switch__handle {
    left: 1em;
  }
  @media screen and (max-width: 768px) {
    & {
      &__title {
        font-size: .875em;
        white-space: normal;
      }
    }
  }
}
</style>
<script>
export default{
	props:{
	  disabled: {
	    type: [Boolean, Number],
      required: false,
      default: false,
    },
		description: {
			type: [String],
			required: false,
		},
		switch: {
			type: Boolean,
		},
		value: {
			required:true
		},
		title: {
			type: String,
			required: false,
		},
	},
	watch:{
		value(newVal) {
			this.val = newVal;
		},
		val(newVal) {
			this.$emit('input',newVal);
		}
	},
	data() {
		return {
			val:this.value
		}
	},
  methods: {
	  onClick() {
	    if (!this.disabled) {
	      this.val = !this.val;
      }
    }
  }
}
</script>
