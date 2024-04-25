<template>
  <div class="page-container">
    <directory-view :directory="directory" :path="$route.path.substring(1)"/>
  </div>
</template>
<script>
import DirectoryView from "@/components/DirectoryView.vue";
export default {
  watch: {
    '$route.name'() {
      console.log('change name');
    }
  },
  async asyncData({app, route}) {
    const directory = await app.$api.get(`${route.path.substring(1)}`);
    return {
      directory
    }
  },
  head () {
    return {
      title: this.$t(this.directory.heading)
    }
  },
  components: {
    DirectoryView,
  },
}
</script>
