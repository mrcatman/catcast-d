<template>
  <div class="preloading-line"></div>
</template>
<style lang="scss" scoped>
.preloading-line {
  background: var(--lighten-3);
  height: .5em;
  width: calc(100% - 1em);
  margin: .5em .5em 1em;
  border-radius: .125em;
  animation: preloadingLine 2s infinite;
  &:nth-of-type(2) {
    animation-delay: .5s;
    opacity: .75;
    width: calc(100% - 5em);
  }
  &:nth-of-type(3) {
    animation-delay: 1s;
    opacity: .9;
    width: calc(100% - 3.5em);
    height: .325em;
  }
  &:nth-of-type(4) {
    height: .325em;
    animation-delay: 1.5s;
    opacity: .5;
    width: calc(100% - 2.5em);
  }
}
@keyframes preloadingLine {
  0% {
    opacity: .5;
  }
  50% {
    opacity: .75;
  }
  100% {
    opacity: .5;
  }
}
</style>
<script>
</script>
