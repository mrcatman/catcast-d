<template>
  <div class="resizable-row" :class="{'resizable-row--active':isResizing}" ref="row_element">
    <slot></slot>
  </div>
</template>
<style lang="scss">
  .resizable-row{
    display: flex;
    width:100%;
    &--active{
      user-select: none;
    }
  }
</style>
<script>
const childTag = 'resizable-row-child';
export default {
    mounted() {
      const childCount = this.$children.filter(component => component.$options._componentTag === childTag).length;
      let widths = null;
      if (this.name) {
        if (localStorage['m_widths_'+this.name]) {
          const decoded = JSON.parse(localStorage['m_widths_'+this.name]);
          if (decoded && Object.keys(decoded).length === childCount) {
            widths = decoded;
            this.widths = widths;
          }
        }
      }
      this.$children.forEach((component,idx)=>{
        if (component.$options._componentTag === childTag) {
          if (widths) {
            let widthInPercents = widths[idx] + '%';
            this.widths[idx] = widths[idx];
            component.$el.style.width = widthInPercents;
          } else {
            if (component.defaultWidth) {
              let widthInPercents = (component.defaultWidth * 100) + '%';
              this.widths[idx] = component.defaultWidth * 100;
              component.$el.style.width = widthInPercents;
            }
          }
        }
      });
    },
    props: {
      name: {
        type:[String],
        required:false,
      }
    },
    data() {
      return {
        widths: {},
        isResizing: false,
      }
    },
    watch: {
      isResizing(resizing) {
       resizing ? this.$emit('resizestart') : this.$emit('resizeend');
       if (!resizing) {
         if (this.name) {
           localStorage['m_widths_'+this.name] = JSON.stringify(this.widths);
         }
       }
      }
    },
    methods: {
      resize(positionX,uid) {
        const row = this.$refs.row_element;
        const rect = row.getBoundingClientRect();
        let idx = this.$children.indexOf(this.$children.filter(component => component._uid === uid)[0]);
        let width = (positionX - rect.left) / row.offsetWidth * 100;
        const changeInWidth = this.widths[idx-1] - width;
        const changedWidth = 100 - this.widths[idx-1];
        this.widths[idx-1] = width;
        this.widths[idx+1] = changedWidth;
        this.$children[idx-1].$el.style.width = width + '%';
        this.$children[idx+1].$el.style.width = changedWidth + '%';
      }
    }
  }
</script>
