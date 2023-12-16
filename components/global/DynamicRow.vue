<template>
  <div class="dynamic-row" ref="row">
    <slot></slot>
  </div>
</template>
<style lang="scss">
.dynamic-row {
  display: flex;
  flex-wrap: wrap;
  > * {
    width: var(--dynamic-row-item-width)!important;
  }
}
</style>
<script>
import ResizeSensor from "css-element-queries/src/ResizeSensor";

export default {
  props: {
    rows: Number,
    itemWidth: {
      type: Number,
      required: false,
      default: 400
    }
  },
  data() {
    return {
      //itemsInRow: 1,
    }
  },
  mounted() {
    this.startResizeWatch();
  },
  methods: {
    onResize(width) {
      const itemsInRow =  Math.ceil(width / this.itemWidth);
      this.$refs.row.style.setProperty(`--dynamic-row-item-width`, `${100 / itemsInRow}%`);
      if (this.rows) {
        const rowHeight = this.$refs.row.children.length > 0 ? this.$refs.row.children[0].offsetHeight : 0;
        this.$refs.row.style.setProperty('height', rowHeight + 'px');
      }
    },
    startResizeWatch() {
      this.onResize(this.$refs.row.offsetWidth);
      new ResizeSensor(this.$refs.row, (e) => {
        this.onResize(e.width);
      });
    },
  }
}
</script>
