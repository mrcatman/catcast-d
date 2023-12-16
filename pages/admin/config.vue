<template>
  <div>
    <c-box>
      <template slot="main">
        <c-form method="put" url="/admin/config" :initialValues="siteConfig" :autoSave="true" :hideSubmit="true">
          <c-multi-picture-uploader
            v-form-input="'smileys'"
            :config="smileysUploaderConfig"
          />
        </c-form>
      </template>
    </c-box>
  </div>
</template>
<script>
import {mapState} from "vuex";

export default {
  computed: {
    ...mapState('config', ['siteConfig']),
    smileysUploaderConfig() {
      return {
        title: this.$t('dashboard.chat.custom_smileys._title'),
        folder: 'smileys',
        max: Infinity,
        nameField: {
          use: true,
          title: this.$t('dashboard.chat.custom_smileys.code'),
          id: 'code',
        },
        small: true
      }
    }
  }
}
</script>
