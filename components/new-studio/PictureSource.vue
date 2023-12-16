<template>
  <BaseSource :disabled="disabled" :zIndex="zIndex" :data="val" @change="onChangePosition" :lockAspectRatio="value.keepAspectRatio">
    <img v-if="value.picture && value.picture.path" :src="value.picture.path" class="picture-source__image" :class="{'picture-source__image--save-proportions': value.keepAspectRatio}"/>
  </BaseSource>
</template>
<style lang="scss">
  .picture-source {
    &__image {
      width: 100%;
      height: 100%;
      &--save-proportions {
        height: unset;
      }
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
                  if (!this.isDragging && !this.isResizing) {
                      if (this.mirrorElement) {
                          this.setMirrorElementPosition();
                          if (newVal.picture && (newVal.picture.path !== this.mirrorElement.src)) {
                              this.mirrorElement.src = newVal.picture.path;
                          }
                      }
                  }
               },
              deep: true
          }
      },
      data() {
          return {
              val: this.value,
              updateTimeout: null,
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
              if (!this.val.keepAspectRatio) {
                  mirrorElement.style.height = this.value.height * 100 + '%';
              } else {
                  mirrorElement.style.height = "";
              }
              mirrorElement.style.left = this.value.x * 100 + '%';
              mirrorElement.style.top = this.value.y * 100 + '%';
          },
          bindElement(mirrorContainer) {
              this.mirrorContainer = mirrorContainer;
              let mirrorElement = document.createElement('img');
              this.mirrorElement = mirrorElement;
              mirrorElement.style.position = 'absolute';
              mirrorElement.style.zIndex = this.zIndex;
              if (this.value.picture && this.value.picture.path) {
                  mirrorElement.src = this.value.picture.path;
              }
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
          BaseSource
      }
  }
</script>
