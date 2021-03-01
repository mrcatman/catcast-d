<template>
  <EditProfile v-if="me" :profile="me" />
</template>

<script lang="ts">
  import Vue from 'vue'
  import EditProfile from '~/components/layout/auth/EditProfile.vue'
  export default Vue.extend({
    components: { EditProfile },
    computed: {
      me() {
        return this.$accessor.modules.auth.me || null;
      },
    },
    methods: {

    },
    data () {
      return {

      }
    },
  })
</script>
