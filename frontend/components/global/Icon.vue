<template>
  <i :class="iconClasses">{{iconText}}</i>
</template>
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
<script>
export default {
  computed: {
    iconClasses() {
      if (this.icon.startsWith('fa-')) {
        return ['fa', this.icon];
      }
      if (this.icon.startsWith('far-')) {
        return ['far', this.icon.replace('far-', 'fa-')];
      }
      if (this.icon.startsWith('fas-')) {
        return ['fas', this.icon.replace('fas-', 'fa-')];
      }
      if (this.icon.startsWith('fab-')) {
        return ['fab', this.icon.replace('fab-', 'fa-')];
      }
      return 'material-icons';
    },
    iconText() {
      return this.icon.startsWith('fa-') || this.icon.startsWith('far-') || this.icon.startsWith('fab-') || this.icon.startsWith('fas-') ? '' : this.icon;
    }
  },
  props: {
    icon: {
      type: String,
      required: true
    },
    // iconType: {
    //   type: String,
    //   required: false,
    //   default: 'material-icons'
    // }
  }
}
</script>
