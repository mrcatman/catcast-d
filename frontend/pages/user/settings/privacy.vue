<template>
  <c-form box :initialValues="user" method="put" url="/auth/me">
    <c-box>
      <template slot="main">
        <!-- todo: remove unused -->
        <c-select v-form-input="'additional_settings.privacy.can_view_profile'" :title="$t('settings.privacy.page.heading')" :options="options.can_view_profile" />
        <c-select v-form-input="'additional_settings.privacy.can_write_on_wall'" :title="$t('settings.privacy.feed.heading')" :options="options.can_write_on_wall"/>
        <c-select v-form-input="'additional_settings.privacy.can_write_messages'" :title="$t('settings.privacy.messages.heading')" :options="options.can_write_messages" />
      </template>
    </c-box>
  </c-form>
</template>
<script>
import {mapState} from "vuex";

export default {
  computed: {
    ...mapState('auth', ['user']),
    options() {
      return {
        can_view_profile: [
          {name: this.$t('settings.privacy.page.options.all_users'), value: 'all'},
          {name: this.$t('settings.privacy.page.options.friends_only'), value: 'friends'},
        ],
        can_write_on_wall: [
          {name: this.$t('settings.privacy.feed.options.all_users'), value: 'all'},
          {name: this.$t('settings.privacy.feed.options.friends_only'), value: 'friends'},
          {name: this.$t('settings.privacy.feed.options.only_you'), value: 'none'},
        ],
        can_write_messages: [
          {name: this.$t('settings.privacy.messages.options.all_users'), value: 'all'},
          {name: this.$t('settings.privacy.messages.options.friends_only'), value: 'friends'},
        ]
      };
    }
  },
}
</script>
