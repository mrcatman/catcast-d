<template>
  <c-input :title="title" v-form-input="name" />
</template>
<script>
export default {
  props: {
    title: String,
    name: {
      type: String,
      required: true
    }
  }
}
</script>
