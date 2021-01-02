<template>
  <div class="list-item__picture" :style="{backgroundImage: `url(${(picture ? picture.full_url : null)}`}"></div>
</template>
<style lang="scss">
  .list-item__picture {
    width: 3em;
    height: 3em;
    background-position: center;
    background-size: contain;
    background-repeat: no-repeat;
  }
</style>
<script lang="ts">
  import Vue, {PropType} from 'vue';
  import Picture from '~/types/Picture'
  export default Vue.extend({
    props: {
      picture: {
        type: Object as PropType<Picture>,
        required: true
      }
    }
  })
</script>
