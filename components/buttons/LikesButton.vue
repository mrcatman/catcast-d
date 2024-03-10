<template>
  <div class="like-button__container" ref="button" :class="{'like-button--bottom': bottom}">
    <c-button class="like-button" :icon="icon" @click="set()"
              :flat="!state.has_liked" :loading="loading" :count="state.count">{{ title }}
    </c-button>
    <c-popup-menu ref="menu" :position="bottom ? 'bottom-left' : 'top-left'" v-if="shortList.length > 0">
      <c-popup-menu-item :key="item.user.id" v-for="item in shortList" :to="'/users/'+item.user.id">
        <div class="popup-menu__item__avatar" :style="{backgroundImage:'url('+item.user.avatar+')'}"></div>
        <span class="popup-menu__item__text">{{ item.user.username }}</span>
      </c-popup-menu-item>
      <div class="like-button__show-all" @click="modalVisible = true">{{ $t('likes.show_all') }}</div>
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
.like-button {
  &__container {
    z-index: 10;
    position: relative;
    display: inline-flex;
  }

  &__show-all {
    width: 100%;
    background: none !important;
    cursor: pointer;
    padding: .5em 0;
    font-size: .875em;
    font-weight: 600;
    transition: all .4s;

    &:hover {
      background: rgba(255, 255, 255, 0.05) !important;
    }
  }
}
</style>
<script>
export default {
  computed: {
    title() {
      if (this.entityType === 'channels' || this.entityType === 'playlists') {
        return this.state.has_liked ? this.$t('likes.heading_unsubscribe') : this.$t('likes.heading_subscribe');
      }
      return this.$t('likes.heading');
    },
    icon() {
      return this.state.has_liked ? 'favorite' : 'favorite_border';
    },
    shortList() {
      return this.state.list.slice(0, 5);
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
        count: 0,
        has_liked: false,
        list: [],
      },
      bottom: false,
      modalVisible: false,
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
    set() {
      if (this.$store.state.auth.loggedIn) {
        this.loading = true;
        this.$api.post(`likes/${this.entityType}/${this.entityId}`, {state: !this.state.has_liked}).then(({new_count}) => {
          this.state.has_liked = !this.state.has_liked;
          this.state.count = new_count;
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
