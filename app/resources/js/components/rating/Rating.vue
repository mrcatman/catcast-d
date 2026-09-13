<template>
  <div class="rating">
    <c-popup-menu ref="menu" position="top-left" v-if="ratingShowUsers(entityType) && ratingState?.last_likes && ratingState?.last_likes.length > 0">
      <c-popup-menu-item :key="item.user.id" v-for="item in ratingState?.last_likes" :to="`/users/${item.user.id}`" :picture="item.user.avatar">
        {{ item.user.username }}
      </c-popup-menu-item>
      <c-popup-menu-item>
        <div class="rating__show-all" @click="showUsersModal">{{ $t('likes.show_all') }}</div>
      </c-popup-menu-item>

    </c-popup-menu>

    <div class="rating__background" :class="{'rating__background--subscribe': isSubscribe, 'rating__background--positive': ratingState?.current_user_like_weight === 1, 'rating__background--negative': ratingState?.current_user_like_weight === -1}"></div>
    <div class="rating__inner">
      <div @click="rate(1)" class="rating__button" :class="{'rating__button--positive': !isSubscribe, 'rating__button--subscribed': isSubscribe && ratingState?.current_user_like_weight === 1, 'rating__button--disabled':!loggedIn, 'rating__button--active': ratingState?.current_user_like_weight === 1}">
        <span class="rating__button__text" v-if="!ratingEnableDislikes(entityType)">{{likeText}}</span>
        <c-preloader v-if="saving && ratingState?.current_user_like_weight === 1" class="rating__button__loading" />
        <c-icon v-else class="rating__button__icon" :icon="likeIcon" />
        <span class="rating__button__count" v-if="!ratingShowSummarized(entityType) && ratingState?.rating_enabled">{{ratingState?.positive_rating}}</span>
      </div>

      <div v-if="ratingShowSummarized(entityType) && ratingEnableDislikes(entityType)" class="rating__count" @click="usersModalVisible = true" :class="{'rating__count--positive': ratingState?.rating > 0,'rating__count--negative': ratingState?.rating  < 0}">
        {{ ratingState?.rating }}
      </div>
      <div v-else-if="ratingEnableDislikes(entityType)" class="delimiter rating__delimiter"></div>

      <div v-if="ratingEnableDislikes(entityType)" @click="rate(-1)" class="rating__button rating__button--negative" :class="{'rating__button--disabled': !loggedIn, 'rating__button--active': ratingState?.current_user_like_weight === -1}">
        <c-preloader v-if="saving && ratingState?.current_user_like_weight === -1" class="rating__button__loading" />
        <c-icon v-else class="rating__button__icon" icon="fa-thumbs-down" />
        <span class="rating__button__count" v-if="!ratingShowSummarized(entityType) && ratingState?.rating_enabled">{{ratingState?.negative_rating}}</span>
      </div>
    </div>

    <div class="rating__proportion" v-if="ratingEnableDislikes(entityType) && !ratingShowSummarized(entityType) && ratingState?.rating_enabled">
      <div class="rating__proportion__likes" :style="{width: `${likesPercent * 100}%`}"></div>
    </div>

  <!--  <c-modal :header="$t('rating.list')" class="rating__modal" v-model="usersModalVisible" no-padding>

    </c-modal> --> <!-- todo -->
  </div>
</template>
<script lang="ts" setup>
const {request, useRequest} = useApi();
const {loggedIn} = storeToRefs(useAuthStore());
const router = useRouter();
const {ratingEnableDislikes, ratingShowUsers, ratingShowSummarized} = useConfigStore();

const props = defineProps<{
  data?: Rating.State,
  entityType: Entities.EntityType;
  entityId: Entities.EntityId;
  entityUuid: string;
}>()

const ratingState = ref<Rating.State>(props.data);

const loading = ref<boolean>(false);
const saving = ref<boolean>(false);


onMounted(() => {
  if (!props.data) {
    loading.value = true;
    request.get(`/likes/:entityType/:entityId`, {}, {
      entityType: props.entityType,
      entityId: props.entityId,
    }).then((state) => {
      ratingState.value = state;
      loading.value = false;
    })
  }
})


const likesPercent = computed(() => {
  return ratingState.value.positive_rating / (ratingState.value.positive_rating + ratingState.value.negative_rating);
})

const isSubscribe = computed(() => {
  return props.entityType === 'channels' || props.entityType === 'playlists';
})

const likeIcon = computed(() => {
  if (ratingEnableDislikes(props.entityType)) {
    return 'fa-thumbs-up';
  }
  return ratingState.value.current_user_has_liked ? 'favorite' : 'favorite_border';
})

const likeText = computed(() => {
  if (isSubscribe.value) {
    return ratingState.value.current_user_has_liked ? t('likes.heading_unsubscribe') : t('likes.heading_subscribe');
  }
  return t('likes.heading');
})

const rate = (weight: number) => {
  if (!loggedIn.value) {
    router.push('/auth/login');
  }
  saving.value = true;

  const data: Rating.Body = {
    entity_type: props.entityType,
    entity_id: props.entityId
  }

  if (weight === ratingState.value.current_user_like_weight) {
    weight = 0;
    data.state = false;
  } else {
    data.weight = weight;
    data.state = true;
  }
  ratingState.value.current_user_like_weight = weight;

  request.post(`/likes/:entityType/:entityId`, {
    body: data
  }, {
    entityType: props.entityType,
    entityId: props.entityId,
  }).then(data => {
    ratingState.value = {
      ...ratingState.value,
      ...data
    }
  }).finally(() => {
    saving.value = false;
  })
}

const showUsersModal = () => {

}
</script>

<style lang="scss">
.rating {
  position: relative;
  padding: .4em .5em;
  border-radius: var(--border-radius);

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
