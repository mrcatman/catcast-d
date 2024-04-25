<template>
  <c-box class="blog-panel">
    <template slot="title">{{title}}</template>
    <template slot="title_buttons" v-if="isEditing">
      <c-button :to="`/blog/${post.id}`">{{$t('blog.back_to_article')}}</c-button>
    </template>
    <template slot="main">
      <c-form  :method="isEditing ? 'put' : 'post'" :url="isEditing ? `/blog/${post.id}` : ''" :initialValues="post">
        <c-input :title="$t('blog.title')" v-form-input="'title'" />
        <c-text-editor :title="$t('blog.text')" v-form-input="'text'" />
        <c-tags-input :title="$t('blog.tags')"  v-form-input="'tags'" />
        <c-picture-uploader :title="$t('blog.cover_picture')" big folder="blog" v-form-input="'pictures_data.cover_picture'" />
      </c-form>
    </template>
  </c-box>
</template>

<style lang="scss">
  .blog-panel {
    max-width: 1000px;
    @media screen and (max-width: 768px) {
      & {
        max-width: 100%;
      }
    }
  }
</style>
<script>
  export default {
    props: {
      data: Object,
    },
    computed: {
      isEditing() {
        return !!this.data;
      }
    },
    mounted() {
      if (this.data) {
        if (this.post.pictures_data && this.post.pictures_data.cover_picture) {
          this.post.cover_picture_id = this.post.pictures_data.cover_picture.id;
        }
      }
    },
    data() {
      return {
        title: this.data ? this.$t('blog.edit_title') : this.$t('blog.add_title'),
        errors: {},
        post: this.data || {
          title: '',
          text: '',
          tags: [],
          pictures_data: {},
        },
      }
    },
    head () {
      return {
        title: this.title,
      }
    },
  }
</script>
