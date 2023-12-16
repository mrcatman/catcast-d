<template>
  <div class="rating">
    <c-popup-menu ref="menu" position="top-left" v-if="ratingShowUsers(entityType) && ratingData.last_likes && ratingData.last_likes.length > 0">
      <c-popup-menu-item :key="item.user.id" v-for="item in ratingData.last_likes" :to="`/users/${item.user.id}`" :picture="item.user.avatar">
        {{ item.user.username }}
      </c-popup-menu-item>
      <c-popup-menu-item>
        <div class="rating__show-all" @click="usersModalVisible = true">{{ $t('likes.show_all') }}</div>
      </c-popup-menu-item>

    </c-popup-menu>

    <div class="rating__background" :class="{'rating__background--subscribe': isSubscribe, 'rating__background--positive': ratingData.current_user_like_weight === 1, 'rating__background--negative': ratingData.current_user_like_weight === -1}"></div>
    <div class="rating__inner">
      <div @click="rate(1)" class="rating__button" :class="{'rating__button--positive': !isSubscribe, 'rating__button--subscribed': isSubscribe && ratingData.current_user_like_weight === 1, 'rating__button--disabled':!loggedIn, 'rating__button--active': ratingData.current_user_like_weight === 1}">
        <span class="rating__button__text" v-if="!ratingEnableDislikes(entityType)">{{likeText}}</span>
        <c-preloader v-if="loading && ratingData.current_user_like_weight !== -1" class="rating__button__loading" />
        <c-icon v-else class="rating__button__icon" :icon="likeIcon" />
        <span class="rating__button__count" v-if="!ratingShowSummarized(entityType) && ratingData.rating_enabled">{{ratingData.positive_rating}}</span>
      </div>

      <div v-if="ratingShowSummarized(entityType) && ratingEnableDislikes(entityType)" class="rating__count" @click="usersModalVisible = true" :class="{'rating__count--positive': ratingData.rating > 0,'rating__count--negative': ratingData.rating  < 0}">
        {{ ratingData.rating }}
      </div>
      <div v-else-if="ratingEnableDislikes(entityType)" class="delimiter rating__delimiter"></div>

      <div v-if="ratingEnableDislikes(entityType)" @click="rate(-1)" class="rating__button rating__button--negative" :class="{'rating__button--disabled': !loggedIn, 'rating__button--active': ratingData.current_user_like_weight === -1}">
        <c-preloader v-if="loading && ratingData.current_user_like_weight === -1" class="rating__button__loading" />
        <c-icon v-else class="rating__button__icon" icon="fa-thumbs-down" />
        <span class="rating__button__count" v-if="!ratingShowSummarized(entityType) && ratingData.rating_enabled">{{ratingData.negative_rating}}</span>
      </div>
    </div>

    <div class="rating__proportion" v-if="ratingEnableDislikes(entityType) && !ratingShowSummarized(entityType) && ratingData.rating_enabled">
      <div class="rating__proportion__likes" :style="{width: `${likesPercent * 100}%`}"></div>
    </div>

    <c-modal :header="$t('rating.list')" class="rating__modal" v-model="usersModalVisible" no-padding>
      <c-thumbs-list ref="list" :config="listConfig">
        <template slot="item" slot-scope="props">
          <c-list-item :to="`/users/${props.item.user.id}`" :picture="props.item.user.avatar" picture-square>
            <template slot="captions">
              <div class="list-item__title">{{ props.item.user.username }}</div>
            </template>
            <template slot="buttons">
              <span class="rating__list__weight" v-if="ratingEnableDislikes(entityType)" :class="{'rating__list__weight--positive': props.item.weight > 0,'rating__list__weight--negative': props.item.weight < 0}">
                {{ props.item.weight > 0 ? `+${props.item.weight}` : props.item.weight }}
              </span>
            </template>
          </c-list-item>
        </template>
      </c-thumbs-list>
    </c-modal>
  </div>
</template>
<style lang="scss">
.rating {
  position: relative;
  padding: .4em .5em;
  border-radius: .25em;

  &__background {
    background: #fff;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: .1;
    &--positive {
      background: var(--positive-color);
    }
    &--positive#{&}--subscribe {
      background: var(--channel-colors-page-buttons);
    }
    &--negative {
      background: var(--negative-color);
    }
  }

  &__inner {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
  }

  &__delimiter {
    margin: 0 .5em;
    height: 1em;
  }

  &__count {
    margin: 0 .5em;
    cursor: pointer;
    font-weight: 500;
    font-size: 1.125em;
    &--positive {
      color: var(--positive-color);
    }

    &--negative {
      color: var(--negative-color);
    }

  }

  &__button {
    font-size: .875em;
    cursor: pointer;
    padding: .325em .75em;
    opacity: .5;
    display: flex;
    align-items: center;
    justify-content: center;
    &--subscribed {
      opacity: 1;
    }
    &--positive#{&}--active {
      opacity: 1;
      color: var(--positive-color);
    }

    &--negative#{&}--active {
      opacity: 1;
      color: var(--negative-color);
    }
    &__loading {
      margin: -.5em 0;
    }
    &__count {
      font-size: 1.0625em;
      font-weight: 500;
      margin-left: .5em;
    }
    &__icon {
      font-size: 1em;
    }
    &__text {
      margin-right: .5em;
    }
  }

  &__proportion {
    height: .1875em;
    background: var(--negative-color);
    margin: .5em -.5em -.5em;

    &__likes {
      height: 100%;
      background: var(--positive-color);
    }
  }

  &__list {
    &__weight {
      font-weight: 600;
      font-size: 1.325em;

      &--positive {
        color: var(--positive-color);
      }

      &--negative {
        color: var(--negative-color);
      }
    }
  }
}

.bright .rating__background {
  background: #000;
}

</style>
<script>
import {mapGetters} from "vuex";

export default {
  props: {
    data: {
      type: Object,
      required: false,
    },
    entityType: {
      type: String,
      required: true,
    },
    entityId: {
      type: Number,
      required: true
    },
    entityUuid: Number,
  },
  data() {
    return {
      list: [],
      ratingData: {},
      loading: false,
      usersModalVisible: false,
    }
  },
  computed: {
    listConfig() {
      return {
        view: 'list',
        url: `/likes/${this.entityType}/${this.entityId}/users`,
        paginate: true,
        infiniteScroll: true,
        innerScroll: true,
        noPadding: true
      }
    },
    likesPercent() {
      return this.ratingData.positive_rating / (this.ratingData.positive_rating + this.ratingData.negative_rating);
    },
    isSubscribe() {
      return this.entityType === 'channels' || this.entityType === 'playlists';
    },
    likeIcon() {
      if (this.ratingEnableDislikes(this.entityType)) {
        return 'fa-thumbs-up';
      }
      return this.ratingData.current_user_has_liked ? 'favorite' : 'favorite_border';
    },
    likeText() {
      if (this.isSubscribe) {
        return this.ratingData.current_user_has_liked ? this.$t('likes._title_unsubscribe') : this.$t('likes._title_subscribe');
      }
      return this.$t('likes._title');
    },
    loggedIn() {
      return this.$store.state.auth.loggedIn;
    },
    ...mapGetters('config', ['ratingEnableDislikes', 'ratingShowSummarized', 'ratingShowUsers']),
  },
  mounted() {
    if (!this.data) {
      this.load();
    } else {
      this.ratingData = this.data;
    }
  },
  methods: {
    load() {
      this.loading = true;
      this.$api.get(`likes/${this.entityType}/${this.entityId}${this.entityUuid ? `?uuid=${this.entityUuid}` : ''}`).then(data => {
        this.ratingData = data;
      }).finally(() => {
        this.loading = false;
      })
    },
    rate(weight) {
      if (this.loggedIn) {
        this.loading = true;
        const data = {
          entity_type: this.entityType,
          entity_id: this.entityId
        };
        if (weight === this.ratingData.current_user_like_weight) {
          weight = 0;
          data.state = false;
        } else {
          data.weight = weight;
          data.state = true;
        }
        this.ratingData.current_user_like_weight = weight;
        this.$api.post(`likes/${this.entityType}/${this.entityId}${this.entityUuid ? `?uuid=${this.entityUuid}` : ''}`, data).then(data => {
          this.ratingData = {
            ...this.ratingData,
            ...data
          }
        }).finally(() => {
          this.loading = false;
        })
      } else {
        this.$router.push('/auth/login');
      }
    },
  }
}
</script>
