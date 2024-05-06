<template>
  <div class="page-container">
    <directory-view :directory="directory" :path="path"/>
  </div>
</template>
<script>
import DirectoryView from "@/components/DirectoryView.vue";
export default {
  watch: {
    '$route.name'() {
      this.$nuxt.refresh();
    }
  },
  async asyncData({app, route}) {
    const path = route.path.substring(1);
    const directory = await app.$api.get(path.length ? path : 'directory/index');
    return {
      path,
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
