<template>
<div class="nothing-found-page">
	<div class="nothing-found-page__inner">
		<div class="nothing-found-page__icon">
			<i class="fa" :class="`fa-${this.icon}`"></i>
		</div>
		<div class="nothing-found-page__title">
			{{this.title !=='' ? this.title : $t('nothingfound.title')}}
		</div>
		<div v-if="sub !== ''" class="nothing-found-page__sub">
			{{sub}}
		</div>
    <div class="nothing-found-page__buttons">
      <slot name="buttons">

      </slot>
    </div>
	</div>
</div>
</template>
<style lang="scss">
.nothing-found-page {
    height: 100%;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    text-shadow: 0 1em 1.75em rgba(255, 255, 255, 0.35);
    color: var(--text-color);
    &__inner {
      text-align: center;
    }
    &__title {
      font-size: 1.75em;
      font-weight: 400;
    }
    &__icon {
      font-size: 5em;
      color: var(--inactive-color);
    }
    &__buttons {
      font-size: 1.25em;
      margin: .5em 0 0;
    }

  @media screen and (max-width: 768px) {
    padding: 0 1em;
  }
}
.bright .nothing-found-page {
  color: rgba(0,0,0,.25);
}
</style>
<script>
export default {
	props: {
		title: {
			type: String,
			required: false,
			default: '',
		},
		sub: {
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
