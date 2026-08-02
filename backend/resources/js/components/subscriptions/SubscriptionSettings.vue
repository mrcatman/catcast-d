<template>
  <div>
    <c-checkbox :key="event.id" v-for="event in events" :title="$t(event.name)" v-form-input="event.id" />
  </div>
</template>
<script>
export default {
  props: {
    events: {
      type: Array,
      required: true
    }
  }
}
</script>
