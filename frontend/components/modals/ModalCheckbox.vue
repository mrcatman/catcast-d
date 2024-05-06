<template>
  <div>
    {{text}}
  <c-checkbox :title="title" v-form-input="inputName" />
  </div>
</template>
<script>
export default {
  props: {
    title: String,
    text: String,
    inputName: {
      type: String,
      required: true
    }
  }
}
</script>
