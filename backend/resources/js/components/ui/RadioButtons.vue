<template>
  <div class="radio-buttons" :class="{'radio-buttons--block': block}">
    <c-input-title v-if="title">{{ title }}</c-input-title> <!-- todo: for other components -->

    <div class="radio-buttons__items" :class="{'radio-buttons__items--inline': inline}">
      <div @click="selectItem(item)" :key="$index" v-for="(item, $index) in values" class="radio-buttons__item" :class="{'radio-buttons__item--active': item.value === model}">
        <span class="radio-buttons__item__circle"></span>
        <span class="radio-buttons__item__text">{{ item.name }}</span>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
.radio-buttons {
  padding: var(--vertical-margin) 0;
  &__items {
    display: flex;
    flex-direction: column;
    &--inline {
      flex-direction: row;
    }
  }

  &__items--inline &__item {
    margin-right: 1em;
  }

  &__item {
    display: flex;
    padding-bottom: var(--vertical-margin);
    cursor: pointer;
    font-weight: 500;
    transition: all .4s;
    &:last-of-type {
      padding-bottom: 0;
    }
    &__circle {
      width: 1.25em;
      height: 1.25em;
      border: .1em solid var(--input-bg-color);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      &:before {
        content: "";
        display: block;
        width: .5em;
        height: .5em;
        background: var(--input-bg-color);
        border-radius: 50%;

      }
    }

    &__text {
      color: var(--inactive-color);
      margin: 0 0 0 .5em;
    }

    &--active &__text {
      color: var(--text-color);
    }

    &--active &__circle {
      border-color: var(--active-color);
    }

    &--active &__circle:before {
      background: var(--active-color);
    }
  }


  &--block &__item {
    background: rgba(0, 0, 0, 0.25);
    margin: 0;
    padding: .5em 1em;
    font-size: 1em;
    transition: all .4s;

    &:hover {
      background: rgba(255, 255, 255, 0.1);

    }

    &__circle {
      display: none;
    }

    &--active {
      background: var(--active-color);
    }

    &__text {
      margin: 0;
      transition: all .4s;
    }
  }

  &--block &__item:hover &__item__text {
    color: #fff;
  }

  &--block &__items {
    border-radius: var(--border-radius);
    overflow: hidden;
    display: inline-flex;
    font-size: 1.125em;
  }

  &--block &__items {
    border-radius: var(--border-radius);
    overflow: hidden;
    display: inline-flex;
    font-size: 1.125em;
  }
}

</style>
<script setup lang="ts">
interface Item {
  name: string;
  value: string | number;
}

const props = defineProps<{
  block?: boolean;
  defaultValue?: any;
  inline?: boolean;
  title?: string;
  values: Item[];
}>();

const model = defineModel<string | number>();
const selectItem = (item: Item) => {
  model.value = item.value;
}

</script>
