<template>
  <div ref="container" class="selection-area-container">
    <slot></slot>
  </div>
</template>
<script>
import SelectionArea from '@viselect/vanilla';

export default {
  methods: {
    enableSelection() {
      const selection = new SelectionArea({
        selectionAreaClass: 'selection-area',
        selectionContainerClass: 'selection-area-container',
        container: this.$refs.container,
        startareas: [this.$refs.container],
        boundaries: [this.$refs.container],
        behaviour: {
          overlap: 'keep',
          startThreshold: 20,
        },
        features: {
          singleTap: {
            allow: false,
          }
        },
        ...this.options
      });
      selection.on('stop', evt => {
        this.$emit('selected', evt.store.selected);
      });
    },
  },
  mounted() {
    this.enableSelection();
  },
  props: {
    options: {
      type: Object,
      required: false,
      default: () => {
        return {}
      }
    }
  }
}
</script>
