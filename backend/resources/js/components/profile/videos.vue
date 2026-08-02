<template>
  <div class="full-height">
    <VideosSection class="profile-page__videos" ref="videos_section" :searchParams="`users/${this.id}/videos`"  />
  </div>
</template>
<style lang="scss">
  .profile-page__videos {
    min-height: 100%;
  }
</style>
<script>
  import VideosSection from '@/components/VideosSection';
  export default {
    components: {
      VideosSection
    },
    data() {
      return {

      }
    },
    props: {
      id: {
        type: [Number, String],
        required: true
      }
    },
    methods: {

    }
  }
</script>
