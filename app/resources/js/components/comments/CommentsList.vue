<template>
  <div class="comments" ref="main">
    <c-box no-padding>
      <template #title>
        {{ $t('comments.heading') }}
      </template>
      <template #title_buttons>
        <c-select v-if="commentsDisplay" v-model="order" class="comments__order-select" :options="orderOptions"/>
      </template>
      <template #main>
        <comments-panel
            :enabled="commentsEnabled"
            :canWriteFromChannelName="canWriteFromChannelName"
            :ban="ban"
            @added="onNewCommentAdded"

            :entity-type="entityType"
            :entity-id="entityId"
        />
        <c-preloader block v-if="loading"/>
        <c-infinite-scroll ref="list" @scroll="loadMore()" :loading="loading" v-show="total > 0" class="comments__list">
          <comment
              :panel-enabled="commentsEnabled"
              :entity-type="entityType"
              :entity-id="entityId"
              :data="comment"
              v-for="comment in comments"
              :key="comment.id"
          />
        </c-infinite-scroll>
        <transition name="fade">
          <div class="comments__pager" v-if="showPager" :style="{right: pagerRightPosition + 'px'}">
            <c-pager :pages-count="pagesCount" v-model="currentPage" @pageChange="load" />
          </div>
        </transition>
      </template>
    </c-box>
  </div>
</template>
<style lang="scss">
.comments {
  position: relative;
  height: 100%;

  &__pager {
    margin-right: 1em;
    position: fixed;
    bottom: 1em;
    z-index: 100;
    transition: opacity .2s;
  }

  &__inner {
    display: flex;
    flex-direction: column;
  }

  &__order-select {
    margin: -.75em 0;
  }

  &__list {
    box-sizing: border-box;
    width: 100%;
    padding: 1em;

    &:empty {
      display: none;
    }
  }
}
</style>
<script lang="ts" setup>
import Comment from '@/components/comments/Comment';
import CommentsPanel from '@/components/comments/CommentsPanel';

const { t } = useI18n();
const route = useRoute();

const {request} = useApi();

const props = defineProps<{
  entityType: Entities.EntityType;
  entityId: Entities.EntityId;
  accessSettings?: Auth.AccessSettings;
}>();

const {
  items: comments,
  total,
  load,
  loadMore,
  loading,
  currentPage,
  setPage,
  showPager,
  pagesCount,
  addItem,
} = usePaginatedData<Api.User>(params => request.get(`/comments/:entityType/:entityId`, {
  query: {
    ...params,
    order: order.value
  }
}, {
  entityType: props.entityType,
  entityId: props.entityId,
}));

const accessSettings = ref<Auth.AccessSettings>(props.accessSettings);
const commentsEnabled = computed(() => {
  return accessSettings.value?.comments_enabled;
})
const commentsDisplay = computed(() => {
  return accessSettings.value?.comments_display;
})
const ban = computed(() => {
  return accessSettings.value?.ban;
})

const canWriteFromChannelName = computed(() => {
  return accessSettings.value?.comments_can_write_from_channel_name;
})

const loadedFirstTime = ref<boolean>(false);

const pagerVisible = ref<boolean>(false);
const pagerRightPosition = ref<number>();

const mainRef = useTemplateRef('main');
const listRef = useTemplateRef('list');

const checkIfShouldScrollToComment = () => {
  const commentId = route.query.comment_id;
  if (!commentId) {
    return;
  }

  mainRef.value.scrollIntoView({
    behavior: 'smooth'
  });
  // todo: load needed comment
}

const loadIfInViewport = async () => {
  if (!loadedFirstTime.value) {
    const rect = mainRef.value.getBoundingClientRect();
    if (rect.top < window.innerHeight) {
      loadedFirstTime.value = true;
      await loadSettings();
      await load();
    }
  }
}

const onScroll = () => {

  if (!listRef.value) {
    return;
  }
  loadIfInViewport();

  const rect = listRef.value.$el.getBoundingClientRect();
  pagerVisible.value = rect.top - 72 < window.innerHeight;
}

const setPagerPosition = () => {
  if (!listRef.value) {
    return;
  }
  const rect = listRef.value.$el.getBoundingClientRect();
  pagerRightPosition.value = window.innerWidth - rect.right
}

const loadSettings = async () => {
  if (!accessSettings.value) {
    accessSettings.value = await request.get(`/access-settings/:entityType/:entityId`, {}, {
      entityType: props.entityType,
      entityId: props.entityId,
    });
  }
}

const onNewCommentAdded = async (comment) => {
  if (currentPage.value !== 1) {
    setPage(1);
  } else {
    addItem(comment);
  }
}

const orderOptions = [
  {'name': t('comments.newest'), 'value': 'new'},
  {'name': t('comments.oldest'), 'value': 'old'},
  {'name': t('comments.most_popular'), 'value': 'popular'},
  {'name': t('comments.most_commented'), 'value': 'commented'}
];
const order = ref<string>('new'); // todo: type
watch(order, () => {
  setPage(1);
})

onMounted(() => {
  window.addEventListener('resize', setPagerPosition);
  window.addEventListener('mousewheel', onScroll);

  loadIfInViewport();
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', setPagerPosition);
  window.removeEventListener('mousewheel', onScroll);
})
</script>
