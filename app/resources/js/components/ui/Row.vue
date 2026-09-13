<template>
  <div class="row" :class="classes">
    <slot></slot>
  </div>
</template>
<script>
export default {
  computed: {
    classes() {
      return [
        this.centered ? 'row--centered' : '',
        this.justify ? `row--justify-${this.justify}` : '',
        this.align ? `row--align-${this.align}` : '',
      ]
    }
  },
  props: {
    centered: Boolean,
    align: {
      type: String,
      required: false,
      default: 'center'
    },
    justify: {
      type: String,
      required: false,
      default: 'start'
    },
  }
}
</script>
<style lang="scss" scoped>
.row {
  display: flex;
  margin: 0 -.5em;
  @media screen and (max-width: 768px) {
    flex-wrap: wrap;
  }
  &--align-start {
    align-items: flex-start;
  }
  &--align-center {
    align-items: center;
  }
  &--align-end {
    align-items: flex-end;
  }
  &--align-stretch {
    align-items: stretch;
  }
  &--justify-center {
    justify-content: center;
  }
  &--justify-start {
    justify-content: flex-start;
  }
  &--justify-between {
    justify-content: space-between;
  }
  &--justify-end {
    justify-content: flex-end;
  }
  &--justify-stretch {
    justify-content: stretch;
  }
  &--wrap {
    flex-wrap:wrap;
  }
  &--vertical {
    flex-direction: column;
  }
}
</style>
