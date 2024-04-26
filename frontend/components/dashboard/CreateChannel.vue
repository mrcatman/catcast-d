<template>
  <div class="create-channel">
    <c-radio-buttons  v-form-input="'channel_type'" :title="$t('dashboard.create.channel_type')"  :block="true" :inline="true" :values="channelTypes" v-show="channelTypes.length > 1" />
    <c-row centered>
      <c-col auto-width>
        <c-picture-uploader :title="$t('dashboard.create.logo')" folder="logos" v-form-input="'logo'"  />
      </c-col>
      <c-col>
        <c-col auto-width>
          <c-input v-form-input="'name'" :title="$t('dashboard.create.name.heading')" :description="$t('dashboard.create.name.description')" />
        </c-col>
        <c-col>
          <c-input v-form-input="'shortname'" @keyup="shortnameChanged = true" :regex="/[^a-zа-я0-9_-]/gi" :prepend="`${siteDomain}/`"  :title="$t('dashboard.create.shortname.heading')" :description="$t('dashboard.create.shortname.description')"/>
        </c-col>
      </c-col>
    </c-row>

    <c-tags-input v-form-input="'tags'" :title="$t('dashboard.create.tags.heading')" :description="$t('dashboard.create.tags.description')"/>
    <c-text-editor v-form-input="'description'" :title="$t('dashboard.info.common.description')" />
	</div>
</template>
<style lang="scss">
  .create-channel {
    margin: .5em 0 -.5em;
  }
</style>
<script>
import {mapGetters} from 'vuex';
import {CHANNEL_TYPE_TV, CHANNEL_TYPE_RADIO} from "@/constants/entity-types";

export default{
  middleware: 'auth',
  computed: {
    ...mapGetters('config', ['siteDomain', 'allowedChannelTypes']),
    channelTypes() {
      const types = [];
      if (this.allowedChannelTypes[CHANNEL_TYPE_TV]) {
        types.push({ value: CHANNEL_TYPE_TV, name: this.$t('channels.tv')});
      }
      if (this.allowedChannelTypes[CHANNEL_TYPE_RADIO]) {
        types.push({ value: CHANNEL_TYPE_RADIO, name: this.$t('channels.radio')});
      }
      return types;
    }
  },
  head () {
    return {
      title: this.$t('dashboard.create.heading'),
    }
  }
}
</script>
