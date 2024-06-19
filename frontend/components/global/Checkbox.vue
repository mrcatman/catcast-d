<template>
<div class="checkbox" :class="{'checkbox--disabled': disabled}" ref="checkbox">
  <input :disabled="disabled" type="checkbox" style="display: none" v-model="val" />
	<div class="checkbox__inner" @click="onClick">
		<div v-if="this.switch" class="checkbox__switch" :class="{'checkbox__switch--active': val}">
			<span class="checkbox__switch__handle"></span>
		</div>
		<div v-else class="checkbox__element" :class="{'checkbox__element--active': val}">
			<span class="checkbox__element__tick" v-show="val"><i class="fa fa-check"></i></span>
		</div>
		<div class="checkbox__title" v-if="title">{{title}}</div>
	</div>
	<div class="checkbox__description" v-if="description">{{description}}</div>
</div>
</template>
<style lang="scss">
.checkbox {
  cursor: pointer;
  padding: .5em 0;
  &--disabled {
    opacity: .5;
  }

  &--in-row {
    margin-right: .75em;
  }

  &__inner {
    display: flex;
    align-items: center;
  }

  &__description {
    margin-top: .5em;
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
    font-size: 1em;
    font-weight: 500;
    white-space: nowrap;
    margin: 0 0 0 .5em;
  }

  &__switch {
    position: relative;
    width: 3em;
    background: var(--darken-5);
    height: 1.5em;
    border-radius: 5em;
    margin-right: 1em;
    &__handle {
      background: var(--lighten-5);
      width: 1.25em;
      height: 1.25em;
      display: inline-block;
      position: absolute;
      left: 0;
      top: .125em;
      transition: all .4s;
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

  .theme-modern &__switch {
    width: 2em;
  }

  .theme-modern &__switch--active &__switch__handle {
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
	props: {
	  disabled: Boolean,
		description: String,
		switch: Boolean,
		value: Boolean,
		title: String,
    color: String,
	},
  mounted() {
    this.$emit('input', this.val);
    this.setColor();
  },
	watch: {
    color() {
      this.setColor();
    },
		value(newVal) {
			this.val = newVal;
		},
		val(newVal) {
			this.$emit('input',newVal);
		}
	},
	data() {
		return {
			val: this.value
		}
	},
  methods: {
    setColor() {
      if (this.color) {
        this.$refs.checkbox.style.setProperty('--active-color', this.color);
      } else {
        this.$refs.checkbox.style.removeProperty('--active-color');
      }
    },
    onClick(e) {
      e.preventDefault();
      e.stopPropagation();
      if (!this.disabled) {
        this.val = !this.val;
      }
    }
  }
}
</script>
