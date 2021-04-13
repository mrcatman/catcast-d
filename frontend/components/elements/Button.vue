<template>
<component :is="to ? 'router-link' : 'span'" class="button" @click="onClick"  :to="to"  :class="buttonClasses">
   <span class="button__content" >
      <span class="button__text">
        <slot></slot>
      </span>
     <i class="button__icon button__icon--right material-icons" v-if="icon && !loading">{{icon}}</i>
     <i class="button__loading-indicator" v-if="loading">
        <m-preloader stroke-width="5" :circle-width="'1.5em'" />
     </i>
      <span class="button__count" v-if="count !== null">{{count}}</span>
   </span>
</component>
</template>
<style lang="scss">
.button {
	cursor: pointer;
	border-radius: 1px;
	font-size: .75em;
	transition: all 0.5s;
	overflow: hidden;
	margin: 0;
  background: var(--active-color);
  height: 3em;
  display: inline-flex;
  align-items: center;
  text-decoration: none;
  &:hover{
    filter: brightness(1.15);
    box-shadow: 0 .5em 1.25em rgba(0,0,0,0.5);
  }
  &--big {
    font-size: .875em;
    width: 100%;
    padding: .5em 0;
    justify-content: center;
  }
	&--disabled{
		cursor: default;
		background: var(--disabled-color);
    box-shadow: none;
    opacity: .5;
		&:hover{
			filter: brightness(1);
			box-shadow: none;
		}
	}
  &__content{
		padding: 1.875em 2em;
		display: flex;
		align-items: center;
		justify-content: center;
		white-space:nowrap;
	}
  &__text {
    margin: -.25em -0;
    font-weight: 500;
  }
	&__icon {
    font-size: 1.25em;
		&--left {
			margin :0 .5em 0 0;
		}
		&--right {
			margin: 0 0 0 .5em;
		}
		&--only {
			margin: 0;
		}
	}
  &__text:empty + &__icon {
    margin: 0;
  }

	&--flat{
    background: rgba(255,255,255,0.05);
		box-shadow: none;
    &:hover {
      background: rgba(255,255,255,0.1);
    }
	}
	&--rounded {
		width: 3em;
		height: 3em;
    padding: 0;
    line-height: 1.5em;
		border-radius: 50%;
	}
	&--rounded &__icon {
    margin: 0;
		font-size: 2em!important;
	}
	&--rounded &__content{
		padding:.5em;
	}
	&__count {
		margin: 0 0 0 .75em;
		font-weight: bold;
    &:empty {
      display: none;
    }
	}
  &__loading-indicator {
    margin: 0 0 -.25em .75em;
  }
  &--positive {
    background: var(--positive-color);
  }
  &--negative {
    background: var(--negative-color);
  }
}

</style>
<script lang="ts">
import Vue, { PropOptions } from 'vue'
 export default Vue.extend({
	props:{
    primary: {
      type: Boolean,
      required: false,
    },
    flat: {
      type: Boolean,
      required: false,
    },
    big: {
      type: Boolean,
      required: false,
    },
    rounded: {
      type: Boolean,
      required: false,
    },
    disabled: {
      type: Boolean,
      required: false,
    },
    loading: {
      type: Boolean,
      required: false,
    },
    icon: {
      type: String,
      required: false
    },
    count: {
      type: Number,
      required: false
    },
    to: {
      type: String,
      required: false
    },
    positive: {
      type: Boolean,
      required: false,
    },
    negative: {
      type: Boolean,
      required: false,
    },
	},
  computed: {
    buttonClasses() {
      return {
        'button--primary': this.primary,
        'button--big': this.big,
        'button--flat': this.flat,
        'button--rounded': this.rounded,
        'button--loading': this.loading,
        'button--disabled': this.disabled,
        'button--positive': this.positive,
        'button--negative': this.negative
      }
    }
  },
	methods: {
    onClick(e: Event): void {
      e.preventDefault();
      if (!this.disabled && !this.loading) {
        this.$emit('click', e);
      }
    }
	}
})
</script>
