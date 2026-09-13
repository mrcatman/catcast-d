<template>
  <div ref="container">
    <div class="tooltip" ref="tooltip" :style="tooltipStyle">
      <slot></slot>
    </div>
  </div>

</template>
<style lang="scss">
.tooltip {
  position: absolute;
  white-space: nowrap;
  background: var(--darken-5);
  padding: .5em;
  z-index: 10001;
  border-radius: var(--border-radius);
  font-size: .875rem;
  font-weight: 500;
  color: var(--text-color);
  text-shadow: none;
  pointer-events: none;
  transition: opacity .2s;

  div {
    margin-bottom: .5em;

    &:last-of-type {
      margin-bottom: 0;
    }
  }
}

.tooltip-container {
  position: relative;
}

</style>
<script lang="ts" setup>
const containerRef = useTemplateRef('container');
const tooltipRef = useTemplateRef('tooltip');

const props = defineProps<{
  position?: 'top-left' | 'top-center' | 'top-right' | 'center-left' | 'center-center' | 'center-right' | 'bottom-left' | 'bottom-center' | 'bottom-right';
  ignoreParentSize?: boolean;
}>()

const top = ref<number>(0);
const left = ref<number>(0);
const visible = ref<boolean>(false);

const positionOffset = computed(() => {
  const position = props.position ? props.position.split('-') : ['top', 'left'];
  const offsetTop = position[0] === 'top' ? -16 : (position[0] === 'center' ? parent.offsetHeight / 2 - 16 : parent.offsetHeight + 16);
  const offsetLeft = position[1] === 'left' ? -16 : (position[1] === 'center' ? parent.offsetWidth / 2 - 16 : parent.offsetWidth + 16);
  return {
    offsetTop,
    offsetLeft
  }
});

const tooltipStyle = computed(() => {
  return {
    top: top.value + 'px',
    left: left.value + 'px',
    opacity: visible.value ? 1 : 0
  }
});

const onMouseEnter = async () => {
  left.value = 0;
  top.value = -1000;
  visible.value = true;

  await nextTick();

  const rect = parent.getBoundingClientRect();
  const {offsetLeft, offsetTop} = positionOffset.value;
  left.value = rect.left + offsetLeft;

  if (left.value + tooltipRef.value.offsetWidth > rect.right && !props.ignoreParentSize) {
    left.value = rect.right - tooltipRef.value.offsetWidth;
  } else if (left + tooltipRef.value.offsetWidth < 0) {
    left.value = rect.left + offsetLeft;
  }
  top.value = rect.top + offsetTop;


}

const onMouseLeave = () => {
  visible.value = false;
}

let parent;
onMounted(() => {
  if (!containerRef.value) {
    return;
  }
  parent = containerRef.value.parentElement;
  parent.classList.add('tooltip-container');
  parent.addEventListener('mouseenter', onMouseEnter);
  parent.addEventListener('mouseleave', onMouseLeave);

  const app = document.querySelector('.tooltips-container') || document.getElementById('app');
  app.appendChild(tooltipRef.value);
})
onBeforeUnmount(() => {
  if (parent) {
    parent.removeEventListener('mouseenter', onMouseEnter);
    parent.removeEventListener('mouseleave', onMouseLeave);
  }
  tooltipRef.value && tooltipRef.value.parentElement && tooltipRef.value.parentElement.removeChild(tooltipRef.value);
})


</script>
