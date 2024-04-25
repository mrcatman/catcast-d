<template>
  <div class="tooltip" ref="tooltip" :style="tooltipStyle">
    <slot></slot>
  </div>
</template>
<style lang="scss">
.tooltip {
  position: absolute;
  white-space: nowrap;
  background: var(--darken-5);
  padding: .5em;
  z-index: 10001;
  border-radius: .25em;
  font-size: .875rem;
  font-weight: 500;
  color: var(--text-color);
  text-shadow: none;
  div {
    margin-bottom: .5em;
    &:last-of-type {
      margin-bottom: 0;
    }
  }
}

.tooltip-container {
  position: relative;
  &:hover .tooltip {
    display: block;
  }
}

</style>
<script>
export default {
  computed: {
    positionOffset() {
      const position = this.position ? this.position.split('-') : ['top', 'left'];
      const offsetTop = position[0] === 'top' ? -16 : this.parent.offsetHeight + 16;
      const offsetLeft = position[1] === 'left' ? -16 : this.parent.offsetWidth + 16;
      return {
        offsetTop,
        offsetLeft
      }
    },
    tooltipStyle() {
      return {
        top: this.top + 'px',
        left: this.left + 'px',
        display: this.visible ? 'inline-block' : 'none'
      }
    }
  },
  watch: {
    $route() {
      this.$refs.tooltip && this.$refs.tooltip.parentElement && this.$refs.tooltip.parentElement.removeChild(this.$refs.tooltip);
    }
  },
  mounted() {
    this.parent = this.$refs.tooltip.parentElement;
    if (!this.parent) return;

    this.parent.classList.add('tooltip-container');
    this.parent.addEventListener('mouseenter', this.onMouseEnter);
    this.parent.addEventListener('mouseleave', this.onMouseLeave);

    const app = document.querySelector('.tooltips-container') || document.getElementById('app');
    app.appendChild(this.$refs.tooltip);
  },
  beforeDestroy() {
    if (this.parent) {
      this.parent.removeEventListener('mouseenter', this.onMouseEnter);
      this.parent.removeEventListener('mouseleave', this.onMouseLeave);
    }
  },
  methods: {
    async onMouseEnter() {
      this.left = 0;
      this.top = -1000;
      this.visible = true;
      await this.$nextTick();

      const { left, top, right } = this.parent.getBoundingClientRect();
      const { offsetLeft, offsetTop } = this.positionOffset;
      this.left = left + offsetLeft;
      if (this.left + this.$refs.tooltip.offsetWidth > right) {
        this.left = right - this.$refs.tooltip.offsetWidth ;
      } else if (this.left  + this.$refs.tooltip.offsetWidth < 0) {
        this.left = left + offsetLeft;
      }
      this.top = top + offsetTop;
    },
    onMouseLeave() {
      this.visible = false;
    }
  },
  data() {
    return {
      visible: false,
      parent: null,
      left: 0,
      top: 0
    }
  },
  props: {
    position: String,
  },
}
</script>
