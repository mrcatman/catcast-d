import Vue from 'vue';
import Component from 'vue-class-component'
import { Watch } from 'vue-property-decorator'

const BaseModalComponent = Vue.extend({
  props: {
    value: Boolean
  },
  data() {
    return {
      visible: this.value
    }
  },
  watch: {
    value(visible) {
      this.visible = visible;
    },
    visible(visible) {
      this.$emit('input', visible);
    }
  }
})


 export default BaseModalComponent;
