<template>
<div>
  <c-box>
    <template slot="title">
      {{$t('statistics._title')}}
    </template>
    <template slot="main">
      <statistics-block entity-type="channels" :entity-id="channel.id"/>
    </template>
  </c-box>
</div>
</template>
<script>
  import statisticsBlock from "@/components/statisticsBlock.vue";
  export default {
    head() {
      return {
        title: this.$t('statistics._title')
      }
    },
    props: {
      channel: {
        type: Object,
        required: true
      }
    },
    components: {
      statisticsBlock,
    },
  }
</script>

<style lang="scss">

</style>
