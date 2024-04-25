<template>
  <div ref="options" class="select__options">
    <div :key="option.value" class="select__option" @click="selectOption(option)" v-for="option in options">
      <div class="select__option__inner">
        <c-checkbox v-if="multiple" v-model="multipleSelection[option.value]" class="select__option__checkbox" />
        {{option.name}}
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.select {
  &__options {
    z-index: 10000;
    font-size: 1em;
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    border-bottom-left-radius: .25em;
    border-bottom-right-radius: .25em;
    max-height: 20em;
    overflow: auto;
    background: var(--box-color);
    border: 1px solid var(--border-color);
    box-sizing: border-box;
  }
  &__option {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
    transition: all 0.35s;

    &:hover {
      background: var(--lighten-1);
    }

    &__inner {
      display: flex;
      align-items: center;
      padding: .75em;

    }
    &__checkbox {
      padding: 0;
      margin-right: .5em;
    }
  }

}
</style>
<script>
export default {
  methods: {
    selectOption(option) {
      this.$emit('selectOption', option);
    }
  },
  props: {
    options: {
      type: Array,
      required: true
    },
    multiple: Boolean,
    multipleSelection: {
      type: Object,
      required: false
    }
  }
}
</script>
