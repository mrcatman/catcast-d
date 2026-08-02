<template>
  <div class="custom-colors" :class="{'bright': bright, 'buttons-bright': buttonsBright}"  ref="main">
    <slot></slot>
  </div>
</template>
<script>
import { isBright } from "@/helpers/colors";

export default {
  props: {
    colorsScheme: {
      type: Object,
      required: true
    }
  },
  mounted() {
    this.setColors();
  },
  watch: {
    colorsScheme() {
      this.setColors();
    }
  },
  methods: {
    setColors() {
      if (!this.colorsScheme) {
        return;
      }
      for (let key in this.colorsScheme) {
        this.$refs.main.style.setProperty(`--channel-colors-${key.replace(/_/g, '-')}`, this.colorsScheme[key]);
        this.$refs.main.style.setProperty(`--channel-colors-${key.replace(/_/g, '-')}--rgb`, this.hexToRgb(this.colorsScheme[key]));
      }
    },
    hexToRgb(color) {
      const bigint = parseInt(color.substring(1), 16);
      const r = (bigint >> 16) & 255;
      const g = (bigint >> 8) & 255;
      const b = bigint & 255;
      return `${r}, ${g}, ${b}`;
    },
  },
  computed: {
    bright() {
      return this.colorsScheme?.page_panels ? isBright(this.colorsScheme.page_panels) : false;
    },
    buttonsBright() {
      return this.colorsScheme?.page_buttons ? isBright(this.colorsScheme.page_buttons) : false;
    },
  }
}
</script>
<style lang="scss">
.custom-colors {
  --menu-color: var(--channel-colors-page-background);
  --active-color: var(--channel-colors-page-buttons);
  --box-color: var(--channel-colors-page-panels);
  --list-container-color: var(--channel-colors-page-panels);
  .popup-menu__inner, .tooltip {
    background: rgba(var(--channel-colors-inside-panels--rgb), .5);
  }
  .modal {
    &__header {
      background: none;
      color: var(--channel-colors-page-headings);
    }
    &__box {
      color: var(--channel-colors-page-texts);
      background: var(--channel-colors-page-panels);
    }
  }

  .box {
    color: var(--channel-colors-page-texts);
  }

  .tag {
    background: var(--channel-colors-page-buttons);
    color: var(--channel-colors-page-buttons-texts) !important;
  }

  .tabs__item--current {
    border-bottom: 2px solid  var(--channel-colors-page-headings);
  }



  .checkbox__element {
    background: var(--channel-colors-page-buttons) !important;
    color: var(--channel-colors-page-buttons-texts) !important;
    &--active {
      background: var(--channel-colors-page-buttons-texts) !important;
      color: var(--channel-colors-page-buttons) !important;
    }
  }

  .comments {
    &__title {
      color: var(--channel-colors-page-headings);
    }
  }

}
</style>
