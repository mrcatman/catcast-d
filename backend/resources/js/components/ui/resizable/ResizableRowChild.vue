<template>
  <div class="resizable-row__child">
    <slot></slot>
  </div>
</template>
<script>
  export default {
    name:'ResizableRowChild',
    props: {
      defaultWidth: {
        type:[Number],
        required:false
      }
    }
  }
</script>
