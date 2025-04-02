<template>
  <div ref="list" class="infinite-scroll">
    <div class="infinite-scroll__top" ref="top"></div>
    <slot></slot>
    <div :style="preloaderStyle"  v-if="loading" class="infinite-scroll__loading">
      <c-preloader />
    </div>
  </div>
</template>
<style lang="scss">
  .infinite-scroll {
    position: relative;
    &__loading {
      margin-top: -4em;
      opacity: .75;
      height: 3em;
      position: fixed;
      bottom: 0;
      width: 100%;
      text-align: center;
      padding: 1em 0;
      background: linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.5));
    }
  }
</style>
<script lang="ts" setup>
const threshold = 36;

const preloaderStyle = ref({
  width: '',
  x: '',
  y: '',
})

const props = defineProps<{
  loading: boolean,
}>();

const emit = defineEmits<{
  (e: 'scroll'): void,
  (e: 'scrollToTop'): void,
}>();

const topRef = useTemplateRef('top');
const listRef = useTemplateRef('list');

const onScroll = () => {
  if (!props.loading) {
    if (listRef.value) {
      const rect = listRef.value.getBoundingClientRect();
      if (Math.abs(rect.bottom - window.innerHeight) < threshold) {
        emit('scroll');
      }

      // todo: check listeners
      const topRect = topRef.value.getBoundingClientRect();
      if (Math.abs(topRect.top - rect.top) < threshold) {
        emit('scrollToTop');
      }
    }
  }
}

const setPreloaderPosition = (() => {
  let rect = listRef.value.getBoundingClientRect();
  let top = rect.top + rect.height;

  if (top > window.innerHeight) {
    top = window.innerHeight;
  }
  preloaderStyle.value = {
    width: `${rect.width}px`,
    x: `${rect.left}px`,
    y: `${top}px`
  }
})

watch(props.loading, (loading) => {
  if (loading) {
    setPreloaderPosition();
  }
})

onMounted(() => {
  document.addEventListener('mousewheel', onScroll);
  document.addEventListener('touchend', onScroll);
})

onBeforeUnmount(() => {
  document.removeEventListener('mousewheel', onScroll);
  document.removeEventListener('touchend', onScroll);
})
</script>
