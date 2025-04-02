<template>
  <i :class="iconClasses">{{iconText}}</i>
</template>
<script lang="ts" setup>
const props = defineProps<{
  icon: string
}>();
const iconClasses = computed(() => {
  if (props.icon.startsWith('fa-')) {
    return ['fa', props.icon];
  }
  if (props.icon.startsWith('far-')) {
    return ['far', props.icon.replace('far-', 'fa-')];
  }
  if (props.icon.startsWith('fas-')) {
    return ['fas', props.icon.replace('fas-', 'fa-')];
  }
  if (props.icon.startsWith('fab-')) {
    return ['fab', props.icon.replace('fab-', 'fa-')];
  }
  return 'material-icons';
})

const iconText = computed(() => {
  return props.icon.startsWith('fa-') || props.icon.startsWith('far-') || props.icon.startsWith('fab-') || props.icon.startsWith('fas-') ? '' : props.icon;
})
</script>

<style lang="scss" scoped>
i {

}
.material-icons {
  font-family: 'Material Icons';
  font-weight: normal;
  font-style: normal;
  display: inline-block;
  line-height: 1;
  text-transform: none;
  letter-spacing: normal;
  word-wrap: normal;
  white-space: nowrap;
  direction: ltr;

  /* Support for all WebKit browsers. */
  -webkit-font-smoothing: antialiased;
  /* Support for Safari and Chrome. */
  text-rendering: optimizeLegibility;

  /* Support for Firefox. */
  -moz-osx-font-smoothing: grayscale;

  /* Support for IE. */
  font-feature-settings: 'liga';
}
</style>
