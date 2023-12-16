<template>
  <BaseSource :disabled="disabled" :zIndex="zIndex" :data="val" @change="onChangePosition">
    <iframe allow="autoplay" v-if="value.showInPreview && !disabled" :src="value.src" class="iframe-source__frame" ref="iframe"  frameborder="0" ></iframe>
    <div v-else class="iframe-source__thumbnail">{{value.src}}</div>
  </BaseSource>
</template>
<style lang="scss">
  .iframe-source {
    &__thumbnail {
      width: 100%;
      height: 100%;
      background: var(--box-color);
    }
    &__frame {
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
      watch: {
          zIndex(newIndex) {
              if (this.mirrorElement) {
                  this.mirrorElement.style.zIndex = newIndex;
              }
          },
          disabled(isDisabled) {
              if (this.mirrorElement) {
                  this.mirrorElement.style.opacity = isDisabled ? 0 : 1;
                  this.mirrorElement.src = isDisabled ? null : this.value.src;
              }
          },
         value: {
              handler(newVal) {
                  this.val = newVal;
                  if (this.mirrorElement) {
                      if (newVal.src !== this.mirrorElement.src) {
                          this.mirrorElement.src = newVal.src;
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
              let mirrorElement = document.createElement('iframe');
              this.mirrorElement = mirrorElement;
              mirrorElement.frameBorder = 0;
              mirrorElement.className = 'iframe_need_resume';
              mirrorElement.dataset.src = this.value.src;
              mirrorElement.style.position = 'absolute';
              mirrorElement.style.objectFit = 'cover';
              mirrorElement.style.zIndex = this.zIndex;
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
