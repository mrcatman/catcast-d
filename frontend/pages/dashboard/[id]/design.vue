<template>
 <div ref="main">
   <c-form box class="dashboard-page__section" :postData="design" method="put" :url="'/channels/'+channel.id" :useAlerts="true">
     <design-editor v-model="design" :channel="channel" :data="channel"  />
   </c-form>
   <div class="vertical-delimiter"></div>
   <logos-editor :channel="channel"/>
  </div>
</template>
<script>
  import DesignEditor from '@/components/dashboard/design/DesignEditor';
  import LogosEditor from "@/components/dashboard/design/LogosEditor";
  export default {
    head() {
      return {
        title: this.$t('dashboard.design.heading')
      }
    },
    components: {
      LogosEditor,
      DesignEditor
    },
    props: {
      channel: {
        type: Object,
        required: true
      }
    },
    data() {
      return {
        loading: false,
        errors: {},
        design: {}
      }
    },
    methods: {

      saveDesign() {
        this.loading = true;
        this.$api.put('/channels/' + this.channel.id, this.design, {notifyOnSuccess: true}).then(() => {
          this.errors = {};
        }).catch((e) => {
          this.errors = e.errors || {};
        }).finally(() => {
          this.loading = false;
        })
      },
    },
  }
</script>
