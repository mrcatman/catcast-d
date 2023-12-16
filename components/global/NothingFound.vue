<template>
<div class="nothing-found" :class="{'nothing-found--small': small}">
	<div class="nothing-found__inner">
		<div class="nothing-found__icon">
			<i class="fa" :class="`fa-${this.icon}`"></i>
		</div>
		<div class="nothing-found__title">
			{{ this.title || $t('nothingfound.title') }}
		</div>
		<div v-if="text" class="nothing-found__text">
			{{text}}
		</div>
    <div class="nothing-found__buttons">
      <slot name="buttons"></slot>
    </div>
	</div>
</div>
</template>
<style lang="scss">
.nothing-found {
  height: 100%;
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1em;
  color: var(--text-color);
  box-sizing: border-box;

  &__inner {
    text-align: center;
  }

  &__title {
    font-size: 1.75em;
    font-weight: 400;
    margin-left: .5em;
  }

  &__icon {
    font-size: 5em;
  }

  &__buttons {
    font-size: 1.25em;
    margin: .5em 0 0;
  }

  &--small &__inner {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: flex-start;
  }
  &--small &__title {
    font-size: 1.125em;
  }
  &--small &__icon {
    font-size: 2em;
    margin-right: .25em;
  }

  @media screen and (max-width: 768px) {
    padding: 0 1em;
  }
}
.bright .nothing-found {
  color: rgba(0, 0, 0, .25);
}
</style>
<script>
export default {
	props: {
    small: Boolean,
		title: String,
		text: {
			type: String,
			required: false,
			default: '',
		},
		icon: {
			type: String,
			required: false,
			default: 'search',
		},
	}
}
</script>
