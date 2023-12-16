<template>
  <div class="notifications-panel" v-click-outside="hidePanel">
    <div @click = "listVisible = !listVisible"  class="user-panel__button">
      <i class="material-icons">notifications</i>
      <span class="user-panel__button__count" v-if="user.unread_notifications_count > 0">{{user.unread_notifications_count}}</span>
    </div>
    <div class="notifications-panel__list" v-if="listVisible">
      <div class="notifications-panel__list__heading">
        <span class="notifications-panel__list__title">{{$t('notifications.list_title')}}</span>
        <c-button class="notifications-panel__list__settings" to="/user/settings/notifications" flat rounded icon-only icon="settings" />
      </div>
      <c-thumbs-list ref="list" :config="listConfig" class="notifications-panel__list__main">
        <template slot="item" slot-scope="props">
          <notification-item :data="props.item"/>
        </template>
      </c-thumbs-list>
    </div>
  </div>
</template>
<script>
  import clickOutside from 'vue-click-outside';
  import { mapState } from 'vuex';
  import NotificationItem from '@/components/layout/notifications/NotificationItem';

  export default {
    computed: {
      ...mapState('auth', ['user'])
    },
    directives: {
      clickOutside
    },
    components: {
      NotificationItem
    },
    data() {
      return {
        listVisible: false,
        listConfig: {
          url: 'notifications',
          view: 'list',
          paginate: true,
          infiniteScroll: true,
          noPadding: true,
          search: true,
          hidePaginator: true,
          usePreloadingListItem: true,
        }
      }
    },
    watch: {
      listVisible(visible) {
        if (visible) {
          this.$store.dispatch('auth/readNotifications');
        }
      }
    },
    methods: {
      hidePanel() {
        if (this.listVisible) {
          this.$emit('hide');
          this.listVisible = false;
        }
      },
   }
  }
</script>
<style lang="scss" scoped>
.notifications-panel {
  position: relative;
  &__list {
    position: absolute;
    right: 0;
    top: 3.5em;
    width: 48em;
    background: var(--menu-color);
    &__heading {
      display: flex;
      align-items: center;
      padding: 1em;
      white-space: nowrap;
      border-bottom: 1px solid var(--border-color);
    }
    &__title {
      font-size: 1.125em;
      font-weight: 600;
    }
    ::v-deep .thumbs-list__heading-container {
      padding: 0;
    }
    ::v-deep .thumbs-list__inner {
      max-height: 30em;
      overflow: auto;
    }
    &__settings {
      margin-left: auto;
    }
  }

  &__nothing-found {
    font-size: .75em;
    white-space: nowrap;
    padding: 1em 3.5em;
  }



  @media screen and (max-width: 768px) {
    &__list {
      position: fixed;
      z-index: 1000000;
      left: 0;
      padding: 0;
      top: 0;
      height: calc(100vh - 6em);
      &__inner {
        height: 100%;
        display: flex;
        flex-direction: column;
        &:before {
          display: none;
        }
      }
      &__items {
        flex: 1;
      }
      &__header {
        background: var(--box-header-color);
        color: var(--active-color);
        font-weight: 500;
        display: flex;
        align-items: center;
        padding: 0 .5em;
        position: relative;
        height: 2.5em;
        &__close {
          top: 0;
          right: .25em;
          position: absolute;
          .button {
            color: var(--active-color)!important;
          }
        }
      }
    }
  }
}
</style>
