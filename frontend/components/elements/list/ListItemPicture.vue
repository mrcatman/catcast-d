<template>
  <PictureBlock class="list-item__picture" :data="picture" :defaultFromConfig="defaultFromConfig"></PictureBlock>
</template>
<style lang="scss">
  .list-item__picture {
    width: 3em;
    height: 3em;
    margin: 0 1em 0 0;
    background-position: center;
    background-size: contain;
    background-repeat: no-repeat;
  }
</style>
<script lang="ts">
  import Vue, {PropType} from 'vue';
  import Picture from '~/types/Picture'
  import PictureBlock from '~/components/layout/PictureBlock.vue'
  export default Vue.extend({
    components: { PictureBlock },
    props: {
      picture: {
        type: Object as PropType<Picture>,
        required: true
      },
      defaultFromConfig: {
        type: String,
        required: false
      }
    }
  })
</script>
