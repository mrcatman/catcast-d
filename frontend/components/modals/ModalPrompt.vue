<template>
  <div>
    {{text}}
    <c-input :title="title" v-form-input="inputName" />
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
