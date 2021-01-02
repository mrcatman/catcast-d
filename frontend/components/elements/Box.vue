<template>
  <div class="box">
    <slot></slot>
  </div>
</template>
<style>
  .box {
    display: block;
    text-decoration: none;
    position: relative;
    color: var(--text-color);
    background: var(--box-color);
    box-shadow: 0 0.5em 1em -0.75em rgba(0,0,0,.5);
    padding: 1em;
  }
</style>
