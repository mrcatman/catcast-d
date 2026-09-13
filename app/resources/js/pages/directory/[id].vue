<template>
  <div class="page-container">
    <directory-view :directory="directory" :path="path"/>
  </div>
</template>
<script lang="ts" setup>
import DirectoryView from "@/components/DirectoryView.vue";

const route = useRoute();
const path = route.path.substring(1);
const query = new URLSearchParams(route.query).toString();
const { data: directory } = await useFetch(`/api/${path.length ? path : 'directory/index'}?${query}`);
</script>
