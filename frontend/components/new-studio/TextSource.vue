<template>
  <BaseSource :disabled="disabled" :zIndex="zIndex" :data="val" @change="onChangePosition">
    <div class="text-source__content" :style="getTextStyle" v-html="getText"></div>
  </BaseSource>
</template>
<style lang="scss">
  .text-source {
    width: 100%;
    height: 100%;
    &__content {
      width: 100%;
      height: 100%;
    }
  }
</style>
<script>
  import BaseSource from "./BaseSource";
  export default {
      props: ['value', 'object', 'zIndex', 'disabled'],
      beforeDestroy() {
          if (this.mirrorElement) {
              this.mirrorElement.remove();
          }
      },
      computed: {
          getText() {
              return this.value && this.value.text ? this.value.text.split('\n').join('<br>') : '';
          },
          getTextStyle() {
              let style = {};
              style.fontSize = this.value.fontSize + 'px';
              style.fontFamily = this.value.fontName;
              style.color = this.value.color;
              style.fontWeight = this.value.fontWeight;
              style.textAlign = this.value.textAlign;
              if (this.value.shadowOn) {
                  style.textShadow = `${this.value.shadowOffsetX}px ${this.value.shadowOffsetY}px ${this.value.shadowBlur}px ${this.value.shadowColor}`
              }
              return style;
          }
      },
      watch: {
          zIndex(newIndex) {
              if (this.mirrorElement) {
                  this.mirrorElement.style.zIndex = newIndex;
              }
          },
          disabled(isDisabled) {
              if (this.mirrorElement) {
                  this.mirrorElement.style.opacity = isDisabled ? 0 : 1;
              }
          },
         value: {
              handler(newVal) {
                  this.val = newVal;
                  if (this.mirrorElement) {
                      this.setMirrorElementPosition();
                      this.mirrorElement.innerHTML = newVal.text;
                      let style = this.getTextStyle;
                      for (let property in style) {
                          this.mirrorElement.style[property] = style[property];
                      }
                  }
              },
              deep: true
          }
      },
      data() {
          return {
              val: this.value,
              mirrorContainer: null,
              mirrorElement: null,
              loaded: false
          }
      },
      methods: {
          onChangePosition(position) {
              for (let key in position) {
                  this.value[key] = position[key];
              }
          },
          setMirrorElementPosition() {
              let mirrorElement = this.mirrorElement;
              if (!mirrorElement) {
                  return;
              }
              mirrorElement.style.width = this.value.width * 100 + '%';
              mirrorElement.style.height = this.value.height * 100 + '%';
              mirrorElement.style.left = this.value.x * 100 + '%';
              mirrorElement.style.top = this.value.y * 100 + '%';
          },
          bindElement(mirrorContainer) {
              this.mirrorContainer = mirrorContainer;
              let mirrorElement = document.createElement('div');
              this.mirrorElement = mirrorElement;
              let style = this.getTextStyle;
              for (let property in style) {
                  mirrorElement.style[property] = style[property];
              }
              mirrorElement.style.position = 'absolute';
              mirrorElement.style.zIndex = this.zIndex;
              mirrorElement.innerHTML = this.getText;
              this.setMirrorElementPosition();
              mirrorContainer.appendChild(mirrorElement);
          },
      },
      mounted() {
          this.$nextTick(() => {
              this.loaded = true;
          })
      },
      components: {
          BaseSource,
      }
  }
</script>
