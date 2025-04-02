<template>
<div class="modal" :class="{'modal--inline': inline, 'modal--no-padding': noPadding}" v-if="value">
	<div class="modal__background" @click="close"></div>
	<div class="modal__box">
		<a v-show="showCloseButton" @click="close" class="modal__close"><c-button ripplecolor="#fff" flat rounded icon="close"></c-button></a>
		<div class="modal__header" v-if="header">{{header}}</div>
		<div class="modal__content">
			<slot name="main"></slot>
			<slot></slot>
		</div>
    <div class="modal__buttons">
      <div class="buttons-row" v-if="$slots.buttons">
        <slot name="buttons"></slot>
      </div>
    </div>

	</div>
</div>
</template>
<style lang="scss">
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 10000000;
  display: flex;
  align-items: center;
  justify-content: center;

  &--inline {
    position: absolute;
  }

  &__background {
    background: rgba(0, 0, 0, 0.35);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 100000;
  }

  &--inline &__background {
    position: absolute;
  }


  &__header {
    font-size: 1.125em;
    font-weight: 500;
    padding: .75em 1em;
    background: var(--darken-2);
    border-bottom: 1px solid var(--lighten-2);
  }

  &__box {
    position: relative;
    z-index: 100000000;
    min-width: 40em;
    max-width: 75vw;
    background: var(--box-color);
    color: var(--text-color);
    @media screen and (max-width: 768px) {
      min-width: unset;
      max-width: 100vw;
    }
  }

  &__buttons {
    padding: 1em;
    background: var(--darken-3);
    &:empty {
      display: none;
    }
  }

  &__content {
    height: 100%;
    max-height: 70vh;
    padding: 1em;
    @media screen and (max-width: 768px) {
      overflow: auto;
    }
  }

  &--no-padding &__content {
    padding: 0;
  }

  &__content &__buttons {
    padding: 1em;
    margin: 0 -1.25em -1.25em;
  }


  &__list-container {
    height: 100%;
    max-height: 70vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }

  &__text {
    padding: 1em 0;
  }

  &__close {
    position: absolute;
    top: .25em;
    right: .5em;
    z-index: 10000;
  }

  .response {
    margin-top: 1em;
  }


}
</style>
<script>
export default{
  props: {
	  inline: Boolean,
		showCloseButton: {
			type: Boolean,
			required: false,
			default: true,
		},
		header: String,
		value: {
			type:Boolean,
			required:false,
			default:()=>{
				return null
			}
		},
    noPadding: Boolean,
	},
	created() {

	},
	methods: {
		close() {
			this.$emit('input',false);
		}
	}
}
</script>
