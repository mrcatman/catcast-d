<template>
  <div class="create-channel">
    <c-radio-buttons  v-model="values.channel_type" :errors="errors.channel_type" :title="$t('dashboard.create.channel_type')"  :block="true" :inline="true" :values="channelTypes" v-show="channelTypes.length > 1" />
    <c-row centered>
      <c-col auto-width>
        <c-picture-uploader :title="$t('dashboard.create.logo')" folder="logos" v-model="values.logo" :errors="errors.logo"  />
      </c-col>
      <c-col>
        <c-col auto-width>
          <c-input v-model="values.name" :errors="errors.name" :title="$t('dashboard.create.name.heading')" :description="$t('dashboard.create.name.description')" />
        </c-col>
        <c-col>
          <c-input v-model="values.shortname" :errors="errors.shortname" :regex="/[^a-zа-я0-9_-]/gi" :prepend="`${siteDomain}/`"  :title="$t('dashboard.create.shortname.heading')" :description="$t('dashboard.create.shortname.description')"/>
        </c-col>
      </c-col>
    </c-row>

    <c-tags-input v-model="values.tags" :errors="errors.tags" :title="$t('dashboard.create.tags.heading')" :description="$t('dashboard.create.tags.description')"/>
    <c-text-editor v-model="values.description" :errors="errors.description" :title="$t('dashboard.info.common.description')" />
	</div>
</template>
<style lang="scss">
  .create-channel {
    margin: .5em 0 -.5em;
  }
</style>
<script lang="ts" setup>
import {CHANNEL_TYPE_TV, CHANNEL_TYPE_RADIO} from "@/constants/entity-types";
const {allowedChannelTypes, siteDomain} = useConfigStore();
const {t} = useI18n();

const channelTypes = computed(() => {
    const types = [];
    if (allowedChannelTypes[CHANNEL_TYPE_TV]) {
        types.push({value: CHANNEL_TYPE_TV, name: t('channels.tv')});
    }
    if (allowedChannelTypes[CHANNEL_TYPE_RADIO]) {
        types.push({value: CHANNEL_TYPE_RADIO, name: t('channels.radio')});
    }
    return types;
});

defineProps<{
    values?: any,
    errors?: any
}>();
</script>
