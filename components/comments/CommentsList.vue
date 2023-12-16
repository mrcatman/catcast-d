<template>
  <div class="comments" ref="main">
    <c-box no-padding>
      <template slot="title">
        {{$t('comments._title')}}
      </template>
      <template slot="title_buttons">
        <c-select v-if="commentsDisplay" v-model="order" class="comments__order-select" :options="orderOptions" />
      </template>
      <template slot="main">
        <comments-panel :canWrite="canWrite" :canWriteFromChannelName="canWriteFromChannelName" :ban="ban" @added="onNewCommentAdded" :entity-type="entityType" :entity-id="entityId" :entityUuid="entityUuid" />
        <c-preloader block v-if="loading" />
        <c-infinite-scroll ref="list" @scroll="loadMore()" v-show="comments && comments.total > 0" class="comments__list">
          <comment
            :can-write="canWrite"
            :entity-type="entityType"
            :entity-id="entityId"
            :data="comment"
            v-for="comment in comments.data"
            :key="comment.id"
          />
        </c-infinite-scroll>
        <transition name="fade">
          <div class="comments__pager" v-if="showPager" :style="pager.style">
            <c-pager :data="comments" v-model="currentPage" @pageChange="load" />
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
      margin: 0;
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
<script>
  import Comment from '@/components/comments/Comment';
  import CommentsPanel from '@/components/comments/CommentsPanel';
  export default {
    computed: {
      baseUrl() {
        return `comments/${this.entityType}/${this.entityId}?order=${this.order}&page=${this.currentPage}${this.entityUuid ? `&uuid=${this.entityUuid}` : ''}`;
      },
      showPager() {
        return this.comments && this.comments.last_page > 1 && this.pager.visible;
      },
      canWrite() {
        return this.currentAccessSettings.comments_enabled;
      },
      commentsDisplay() {
        return this.currentAccessSettings.comments_display;
      },
      canWriteFromChannelName() {
        return this.currentAccessSettings.comments_can_write_from_channel_name;
      },
      ban() {
        return this.currentAccessSettings.ban;
      }
    },
    components: {
      Comment,
      CommentsPanel
    },
    props: {
      entityType: {
        type: String,
        required: true
      },
      entityId: {
        type: Number,
        required: true
      },
      accessSettings: Object,
      entityUuid: Number
    },
    data() {
      return {
        currentAccessSettings: {
          ban: null,
          comments_display: false,
          comments_enabled: false,
          permissions: {}
        },
        currentPage: 1,
        loadedFirstTime: false,
        loading: false,
        order: 'new',
        comments: {},
        orderOptions: [
          {'name': this.$t('comments.newest'), 'value': 'new'},
          {'name': this.$t('comments.oldest'), 'value': 'old'},
          {'name': this.$t('comments.most_popular'), 'value': 'popular'},
          {'name': this.$t('comments.most_commented'), 'value': 'commented'},
        ],
        pager: {
          visible: false,
          style: {
            right: 0,
          }
        }
      }
    },
    watch: {
      order() {
        this.load();
      }
    },
    beforeDestroy() {
      window.removeEventListener('resize', this.onResize);
      window.removeEventListener('mousewheel', this.onScroll);
    },
    async mounted() {
      await this.loadSettings(true);
      await this.loadIfInViewport();
      window.addEventListener('resize', this.onResize);
      window.addEventListener('mousewheel', this.onScroll);
      this.onResize();
      this.onScroll();
    },
    methods: {
      async loadIfInViewport() {
        if (!this.loadedFirstTime) {
          const rect = this.$refs.main.getBoundingClientRect();
          if (rect.top < window.innerHeight) {
            this.loadedFirstTime = true;
            await this.loadSettings();
            await this.load();
          }
        }
      },
      onScroll() {
        this.loadIfInViewport();
        if (!this.$refs.list?.$el) {
          return;
        }
        const rect = this.$refs.list.$el.getBoundingClientRect();
        this.pager.visible = rect.top - 72 < window.innerHeight;
      },
      onResize() {
        if (!this.$refs.list?.$el) {
          return;
        }
        const rect = this.$refs.list.$el.getBoundingClientRect();
        const right = window.innerWidth - rect.right;
        this.pager.style.right = right + 'px';
      },
      async loadSettings(onlyIfPreloaded = false) {
        if (this.accessSettings) {
          this.currentAccessSettings = this.accessSettings;
          return;
        }
        if (onlyIfPreloaded) {
          return;
        }
        this.currentAccessSettings = await this.$api.get(`/access-settings/${this.entityType}/${this.entityId}`);
      },
      async load(page) {
        if (page) {
          this.currentPage = page;
        }
        this.loading = true;
        this.comments = await this.$api.get(`${this.baseUrl}`, {onError: this.$api.defaultPaginator});
        this.loading = false;
        page && this.$refs.main && this.$refs.main.scrollIntoView();
      },
      async loadMore() {
         if (!this.loading && this.currentPage < this.comments.last_page) {
          this.loading = true;
          this.currentPage++;
          const data = await this.$api.get(`${this.baseUrl}`, {onError: this.$api.defaultPaginator});
          this.comments.data = [...this.comments.data, ...data.data];
          this.loading = false;
        }
      },
      async onNewCommentAdded(comment) {
        if (this.currentPage !== 1) {
          this.currentPage = 1;
          await this.load();
          await this.$nextTick();
        } else {
          this.comments.data.unshift(comment);
        }
      }
    }
  }
</script>
