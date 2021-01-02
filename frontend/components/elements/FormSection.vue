<template>
  <div class="form-section">
    <h2 class="form-section__title" v-if="title">{{title}}</h2>
    <div class="form-section__content">
      <slot></slot>
    </div>
  </div>
</template>
<script>
  export default {
    props: {
      title: {
        type: String,
        required: false
      }
    }
  }
</script>
<style lang="scss">
  .form-section {
    padding: 0 0 1em;
    margin: 0 0 1em;
    border-bottom: 1px solid rgba(255, 255, 255, .1);
    &:last-of-type {
      margin-bottom: 0;
      padding-bottom: 0;
      border-bottom-width: 0;
    }
    &__title {
      font-weight: 500;
      font-size: 1.375em;
      margin: 0;
      padding: 0 0 .5em;
    }
  }
</style>
