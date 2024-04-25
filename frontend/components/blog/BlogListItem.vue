<template>
  <nuxt-link :to="`/blog/${data.id}`" class="blog-list-item">
    <div class="blog-list-item__title">{{data.title}}</div>
    <div class="blog-list-item__short-text" v-html="data.short_text"></div>
    <div class="blog-list-item__info">
      <c-statistics-icons :data="[
          {icon: 'access_time', value: formatPublishDate(data.created_at), tooltip: formatFullDate(data.created_at)},
          {icon: 'remove_red_eye', value: data.views},
          {icon: 'thumb_up', value: data.likes_count},
          {icon: 'message', value: data.comments_count}
       ]"></c-statistics-icons>
    </div>
  </nuxt-link>
</template>
<style lang="scss">
.blog-list-item {
  max-width: 800px;
  display: block;
  text-decoration: none;
  font-weight: 300;
  border-bottom: 1px solid var(--lighten-2);
  padding: 1.75em 0;
  margin: 0 auto;
  &__title {
    font-size: 1.5em;
    font-weight: 400;
    transition: opacity .25s;
  }

  &__short-text {
    margin: .5em 0;
    line-height: 1.5;
    transition: opacity .25s;
  }
  &:hover &__title, &:hover &__short-text{
    opacity: .875;
  }
  &__info {
    font-weight: 400;
  }
}
</style>
<script>
import { formatPublishDate, formatFullDate } from "@/helpers/dates";
export default {
  methods: {
    formatPublishDate,
    formatFullDate,
  },
  props: {
    data: {
      type: Object,
      required: true
    }
  }
}
</script>
