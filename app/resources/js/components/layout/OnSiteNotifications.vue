<template>
  <div class="notifications-list">
    <div class="notifications-list__item" v-for="notification in list" :key="notification.randomId">
      <c-button transparent narrow icon-only icon="close" class="notifications-list__item__close" @click="close(notification.randomId)"/>
      <nuxt-link :to="notification.url" v-if="notification.picture" :style="{backgroundImage: `url(${notification.picture})`}" class="notifications-list__item__picture"></nuxt-link>
      <nuxt-link :to="notification.url" class="notifications-list__item__texts">
        <c-translated-message tag="div" class="notifications-list__item__title" :message="notification.title"/>
        <div class="notifications-list__item__text">{{ notification.text }}</div>
      </nuxt-link>
    </div>
  </div>
</template>
<style lang="scss">
.notifications-list {
  position: fixed;
  bottom: .5em;
  right: .5em;
  z-index: 10;

  &__item {
    background: var(--notifications-background-color);
    display: flex;
    margin-top: .5em;
    border: 1px solid var(--border-color);
    padding: 1em 2.5em 1em 1em;
    position: relative;
    animation: showNotification .2s forwards;
    &__close {
      position: absolute!important;
      top: .5em!important;
      right: .5em!important;
    }
    &__picture {
      width: 3em;
      height: 3em;
      background-size: contain;
      background-position: center center;
      background-repeat: no-repeat;
      margin-right: 1em;
    }
    &__texts {
      text-decoration: none;
    }
    &__title {
      font-weight: 600;
      margin-bottom: .25em;
    }
  }
}

@keyframes showNotification {
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
import {mapState, mapActions} from 'vuex';

export default {
  computed: {
    ...mapState('notifications', ['list']),
  },
  methods: {
    ...mapActions('notifications', ['close']),
  },
}
</script>
