<template>
  <div class="resizable-row__bar">
    <div class="resizable-row__bar__element" ref="bar_element" @drag.prevent="onDrag" @dragstart="isDragging = true" @dragend="isDragging = false"></div>
  </div>
</template>
<style lang="scss">
  .resizable-row__bar {
    margin: 0 .5em;
    &__element {
      background: rgba(255, 255, 255, 0.35);
      width: .25em;
      box-shadow: 0 0 10px -1px #000;
      height: 100%;
      cursor: col-resize;
    }
  }
</style>
<script>
export default {
  data() {
    return {
      isDragging: false
    }
  },
  mounted() {
    const el = this.$refs.bar_element;
    const parent = this.$parent;
    const uid = this._uid;
    el.onmousedown = function(e) {
      parent.isResizing = true;
      moveAt(e);
      function moveAt(e) {
        let positionX = e.pageX - el.offsetWidth;
        parent.resize(positionX,uid);
      }
      document.onmousemove = function(e) {
        moveAt(e);
      };
      el.onmouseup = document.onmouseup = function() {
        parent.isResizing = false;
        document.onmousemove = null;
        el.onmouseup = null;
      }
    }
  },
  methods: {
    onDrag(e) {
      return false;
    }
  }
}
</script>
