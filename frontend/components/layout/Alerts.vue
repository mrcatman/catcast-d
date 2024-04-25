<template>
<div class="alerts-list">
	<div v-for="alertItem in alerts" :key="alertItem.id" class="alerts-list__item" :class="{'alerts-list__item--success': alertItem.status === 1, 'alerts-list__item--fail': alertItem.status === 0}">
		<c-translated-message :message="alertItem.text" />
	</div>
</div>
</template>
<style lang="scss">
.alerts-list {
  position: fixed;
  bottom: .5em;
  left: .5em;
  z-index: 10000000;

  &__item {
    padding: .75em 1em;
    font-size: .875em;
    font-weight: 400;
    max-width: 50vw;
    line-height: 1.4;
    animation: showAlert .25s linear forwards;
    @media screen and (max-width: 768px) {
      max-width: 90%;
    }
    .theme-default & {
      box-shadow: 0 .5em 1em -.5em rgba(0, 0, 0, 0.75);
    }

    &--success {
      color: #fff;
      background: var(--positive-color);
    }
    &--fail {
      color: #fff;
      background: var(--negative-color);
    }
  }
}
@keyframes showAlert {
  0% {
    opacity: 0;
    transform: translateY(1em);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
<script>
import {mapState} from 'vuex';
export default {
  computed: mapState(['alerts'])
}
</script>
