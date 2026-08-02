<template>
  <div class="base-source" ref="container" v-show="!disabled">
    <vue-draggable-resizable v-if="loaded" :style="{'z-index': zIndex}" ref="resizable"  :w="position.width" :h="position.height" :x="position.x" :y="position.y" @dragging="isDragging = true" @resizing="isResizing = true" @dragstop="onDrag" @resizestop="onResize" :parent="false" :lock-aspect-ratio="false">
      <slot></slot>
    </vue-draggable-resizable>
  </div>
</template>
<style lang="scss">
  .base-source {
    width: 100%;
    height: 100%;
  }
</style>
<script>
  import VueDraggableResizable from 'vue-draggable-resizable'
  import 'vue-draggable-resizable/dist/VueDraggableResizable.css'
  export default {
      props: ['data', 'zIndex', 'disabled'],
      beforeDestroy() {
          if (this.mirrorElement) {
              this.mirrorElement.remove();
          }
      },
      watch: {
          data: {
              handler(newVal) {
                  clearTimeout(this.updateFromOutsideTimeout);
                  if (!this.isDragging && !this.isResizing) {
                      this.updateFromOutsideTimeout = setTimeout(() => {
                          this.onUpdateFromOutside();
                      }, 500);
                  }
              },
              deep: true
          }
      },
      data() {
          return {
              loaded: false,
              position: {
                  width: 1,
                  height: 1,
                  x: 0,
                  y: 0
              },
              isDragging: false,
              isResizing: false,
              updateFromOutsideTimeout: null,
          }
      },
      methods: {
          onUpdateFromOutside() {
              let containerWidth = this.$refs.container.clientWidth;
              let containerHeight = this.$refs.container.clientHeight;
              this.position = {
                  x: Math.ceil(this.data.x * containerWidth),
                  y: Math.ceil(this.data.y * containerHeight),
                  width: Math.ceil(this.data.width * containerWidth),
                  height: Math.ceil(this.data.height * containerHeight),
              }
          },
          updateData() {
              let containerWidth = this.$refs.container.clientWidth;
              let containerHeight = this.$refs.container.clientHeight;
              let data = {
                  width: this.position.width / containerWidth,
                  height: this.position.height / containerHeight,
                  x: this.position.x / containerWidth,
                  y: this.position.y / containerHeight,
              }
              this.$emit('change', data);
          },
          onResize(x, y, width, height) {
              this.position = {
                  x,
                  y,
                  width,
                  height
              }
              this.updateData();
              this.$nextTick(() => {
                  this.isResizing = false;
              });
          },
          onDrag(x, y) {
              this.position.x = x;
              this.position.y = y;
              this.updateData();
              this.$nextTick(() => {
                  this.isDragging = false;
              })
          },
      },
      mounted() {
          if (this.data) {
              let containerWidth = this.$refs.container.clientWidth;
              let containerHeight = this.$refs.container.clientHeight;
              this.position = {
                  x: this.data.x * containerWidth,
                  y: this.data.y * containerHeight,
                  width: this.data.width * containerWidth,
                  height: this.data.height * containerHeight,
              }
          }
          this.loaded = true;
      },
      components: {
          VueDraggableResizable
      }
  }
</script>
