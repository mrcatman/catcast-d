<template>
  <div class="subscribe-button__container" ref="button">

    <c-button :icon="icon" @click="set()" :flat="!state.current_user_has_liked" :loading="loading" :count="state.rating">{{ title }}</c-button>
    <c-button icon-only icon="notifications" @click="showNotificationsSettings()" flat :disabled="!state.current_user_has_liked" :loading="loadingNotificationsSettings">
      <template slot="tooltip">
        <c-tooltip position="bottom-left" v-if="state.current_user_has_liked">{{$t('notifications.settings')}}</c-tooltip>
      </template>
    </c-button>


    <c-popup-menu ref="menu" :position="bottom ? 'bottom-left' : 'top-left'" v-if="shortList.length > 0">
      <c-popup-menu-item :key="item.user.id" v-for="item in shortList" :to="'/users/'+item.user.id">
        <div class="popup-menu__item__avatar" :style="{backgroundImage:'url('+item.user.avatar+')'}"></div>
        <span class="popup-menu__item__text">{{ item.user.username }}</span>
      </c-popup-menu-item>
      <div class="subscribe-button__show-all" @click="modalVisible = true">{{ $t('likes.show_all') }}</div>
    </c-popup-menu>

    <c-modal v-model="modalVisible" :title="$t('likes.title')">
      <div class="modal__users-list">
        <nuxt-link :key="item.user.id" :to="'/users/'+item.user.id" v-for="item in state.list"
                   class="modal__users-list__item">
          <div class="modal__users-list__item__inner">
            <div class="modal__users-list__item__avatar" :style="{backgroundImage: `url(${item.user.avatar})`}"></div>
            <div class="modal__users-list__item__username">{{ item.user.username }}</div>
          </div>
        </nuxt-link>
      </div>
    </c-modal>
  </div>
</template>
<style lang="scss">
.subscribe-button {
  &__container {
    z-index: 10;
    position: relative;
    display: inline-flex;
  }

  &__show-all {
    width: 100%;
    background: none;
    cursor: pointer;
    padding: .5em 0;
    font-size: .875em;
    font-weight: 600;
    transition: all .4s;

    &:hover {
      background: var(--lighten-1);
    }
  }
}
</style>
<script>
import SubscriptionSettings from "@/components/subscriptions/SubscriptionSettings.vue";

export default {
  computed: {
    title() {
      return this.state.current_user_has_liked ? this.$t('likes.title_unsubscribe') : this.$t('likes.title_subscribe');
    },
    icon() {
      return this.state.current_user_has_liked ? 'favorite' : 'favorite_border';
    },
    shortList() {
      return this.state.list?.slice(0, 5) || [];
    }
  },
  props: {
    data: Object,
    entityType: {
      type: String,
      required: true,
    },
    entityId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      loading: false,
      state: this.data || {
        rating: 0,
        current_user_has_liked: false,
        list: [],
      },
      bottom: false,
      modalVisible: false,
      loadingNotificationsSettings: false,
    }
  },
  async mounted() {
    if (!this.data) {
      this.loading = true;
      this.state = await this.$api.get(`likes/${this.entityType}/${this.entityId}`);
      this.loading = false;
    }
    let rect = this.$refs.button.getBoundingClientRect();
    if (rect.top <= 200) {
      this.bottom = true;
    }
  },
  methods: {
    async showNotificationsSettings() {
      this.loadingNotificationsSettings = true;
      const subscriptions = await this.$api.get(`notifications/subscriptions/${this.entityType}/${this.entityId}`);
      const eventCategories = (await this.$api.get('notifications/events'));
      const events = eventCategories.filter(category => category.entity_type === this.entityType)[0]?.events || [];

      const notificationSubscriptionsState = {};
      events.forEach(event => {
        notificationSubscriptionsState[event.id] = subscriptions.some(subscription => subscription.event_type === event.id);
      })

      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        buttonColor: '',
        buttonText: this.$t('global.save'),
        title: this.$t('notifications.heading'),
        component: SubscriptionSettings,
        props: {
          events
        },
        formValues: notificationSubscriptionsState,
        fn: async (typesMap) => {
          const eventTypes = [];
          Object.keys(typesMap).forEach(type => {
            if (typesMap[type]) {
              eventTypes.push(type);
            }
          })
          await this.$api.post(`notifications/subscriptions/${this.entityType}/${this.entityId}`, {
            event_types: eventTypes
          })
        },
      })
      this.loadingNotificationsSettings = false;
    },
    set() {
      if (this.$store.state.auth.loggedIn) {
        this.loading = true;
        this.$api.post(`likes/${this.entityType}/${this.entityId}`, {state: !this.state.current_user_has_liked}).then(({current_user_has_liked, rating}) => {
          this.state.current_user_has_liked = current_user_has_liked;
          this.state.rating = rating;
        }).finally(() => {
          this.loading = false;
        })
      } else {
        this.$router.push('/login');
      }
    }
  }
}
</script>
