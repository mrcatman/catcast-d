<template>
  <div class="dashboard__chat">


    <c-box>
      <template slot="title">{{ $t('dashboard.chat.settings.heading') }}</template>
      <template slot="main">
        <c-form method="put" :url="`/channels/${channel.id}`" :initialValues="channel">
          <c-checkbox v-form-input="'additional_settings.chat.disabled'"
                      :title="$t('dashboard.chat.settings.disabled')"/>
          <div class="vertical-delimiter"></div>
          <c-row>
            <c-col auto-width>
              <c-checkbox v-form-input="'additional_settings.chat.allow_guests'"
                          :title="$t('dashboard.chat.settings.allow_guests')"/>
            </c-col>
            <c-col>
              <c-input v-form-show="'additional_settings.chat.allow_guests'"
                       v-form-input="'additional_settings.chat.default_guest_username'"
                       :title="$t('dashboard.chat.settings.default_guest_username')"/>
            </c-col>
          </c-row>

          <div class="vertical-delimiter"></div>
          <c-input v-form-input="'additional_settings.chat.motd'" :title="$t('dashboard.chat.settings.motd')"/>
          <div class="vertical-delimiter"></div>
          <c-list-input v-form-input="'additional_settings.chat.forbidden_words'" :fields="[{id: 'word'}]"
                        :title="$t('dashboard.chat.settings.forbidden_words.heading')"
                        :description="$t('dashboard.chat.settings.forbidden_words.description')"/>
        </c-form>
      </template>
    </c-box>

    <c-form method="put" :url="'/channels/'+channel.id" :initialValues="channel" :auto-save="true" :hide-submit="true"
            v-if="maxCustomSmileysCount > 0">
      <c-multi-picture-uploader
        v-form-input="'additional_settings.chat.smileys'"
        :config="customSmileysUploaderConfig"
      />
    </c-form>


  </div>

</template>
<style lang="scss">

</style>
<script>

import {mapGetters} from "vuex";


export default {
  head() {
    return {
      title: this.$t('dashboard.chat.heading')
    }
  },
  computed: {
    ...mapGetters('config', ['maxCustomSmileysCount']),
    customSmileysUploaderConfig() {
      return {
        title: this.$t('dashboard.chat.custom_smileys.heading'),
        description: this.$t('dashboard.chat.custom_smileys.description', {count: this.maxCustomSmileysCount}),
        folder: 'smileys',
        max: this.maxCustomSmileysCount,
        nameField: {
          use: true,
          title: this.$t('dashboard.chat.custom_smileys.code'),
          id: 'code',
        }
      }
    },
  },
  props: {
    channel: {
      type: Object,
      required: true
    }
  },
  data() {
    return {}
  },
}
</script>
