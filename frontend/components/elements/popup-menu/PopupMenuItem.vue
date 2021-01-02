<template>
  <div @click="onClick" class="popup-menu__item" :class="{'popup-menu__item--selected': selected}">
    <slot></slot>
  </div>
</template>
<script>
  export default {
    props: {
      selected: {
        type: [Boolean],
        required: false
      },
      to: {
        type: [String],
        required: false,
      }
    },
    methods: {
      onClick(e) {
        if (this.to) {
          this.$router.push(this.to);
        }
        this.$emit('click', e);
      }
    }
  }
</script>
