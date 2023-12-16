<template>
  <div class="full-height">
    <c-preloader v-if="loadingInitial"  />
    <div class="profile-page__blog" v-else>
      <c-nothing-found v-if="blog.data.length === 0" :title="$t('global.nothing_found')"/>
      <c-infinite-scroll :loading="loading" @scroll="loadMore" >
        <blog-list-item v-for="item in blog.data" :data="item" :key="item.id" />
      </c-infinite-scroll>
   </div>
  </div>
</template>

<script>

  import BlogListItem from "@/components/blog/BlogListItem";
  export default {
    components: {
      BlogListItem
    },
    async mounted() {
      this.blog = await this.$api.get(`users/${this.id}/blog`);
      this.loadingInitial = false;
    },
    data() {
      return {
        currentPage: 1,
        loading: false,
        loadingInitial: true,
        blog: {},
      }
    },
    props: {
      id: {
        type: Number,
        required: true
      }
    },
    methods: {
      loadMore() {
        console.log('load');
        if (!this.loading) {
          if (this.currentPage < this.blog.last_page) {
            this.currentPage++;
            this.loading = true;
            this.$api.get(`users/${this.id}/blog?page=${this.currentPage}`).then(res => {
              this.blog.data = [...this.blog.data, ...res.data];
              this.loading = false;
            })
          }
        }
      }
    }
  }
</script>
