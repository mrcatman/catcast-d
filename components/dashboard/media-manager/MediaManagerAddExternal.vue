<template>
  <div>
    <c-input v-form-input="'url'" v-form-validate="'required'" :title="$t('dashboard.media.external.url')" />
    <div v-html="$t('dashboard.media.external.description', {url: ytdlSupportedSitesURL})"></div>
  </div>
</template>
<script>
const ytdlSupportedSitesURL = 'https://ytdl-org.github.io/youtube-dl/supportedsites.html';
export default {
  data() {
    return {
      ytdlSupportedSitesURL
    }
  },
}
</script>
