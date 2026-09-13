<template>
  <div class="dashboard__chat">
    <c-box>
      <template #title>{{ $t('dashboard.chat.settings.heading') }}</template>
      <template #main>
        <c-form-v2 :handler="save" :initial-values="channel">
          <template #default="{ values, errors }">
            <c-checkbox
                v-model="values.additional_settings.chat.disabled"
                :errors="errors['additional_settings.chat.disabled']"
                :title="$t('dashboard.chat.settings.disabled')"
            />
            <div class="vertical-delimiter"></div>
            <c-row>
              <c-col auto-width>
                <c-checkbox
                    v-model="values.additional_settings.chat.allow_guests"
                    :errors="errors['additional_settings.chat.allow_guests']"
                    :title="$t('dashboard.chat.settings.allow_guests')"
                />
              </c-col>
              <c-col>
                <c-input
                    v-if="values.additional_settings.chat.allow_guests"
                    v-model="values.additional_settings.chat.default_guest_username"
                    :errors="errors['additional_settings.chat.default_guest_username']"
                    :title="$t('dashboard.chat.settings.default_guest_username')"
                />
              </c-col>
            </c-row>

            <div class="vertical-delimiter"></div>
            <c-input
                v-model="values.additional_settings.chat.motd"
                :errors="errors['additional_settings.chat.motd']"
                :title="$t('dashboard.chat.settings.motd')"
            />
            <div class="vertical-delimiter"></div>
            <c-list-input
                v-model="values.additional_settings.chat.forbidden_words"
                :errors="errors['additional_settings.chat.forbidden_words']"
                :fields="[{id: 'word'}]"
                :title="$t('dashboard.chat.settings.forbidden_words.heading')"
                :description="$t('dashboard.chat.settings.forbidden_words.description')"
            />
          </template>
        </c-form-v2>
      </template>
    </c-box>

    <c-form-v2
        v-if="maxCustomSmileysCount > 0"
        :handler="save"
        :initial-values="channel"
        auto-save
        :show-submit="false"
    >
      <template #default="{ values, errors }">
        <c-multi-picture-uploader
            v-model="values.additional_settings.chat.smileys"
            :errors="errors['additional_settings.chat.smileys']"
            :config="customSmileysUploaderConfig"
        />
      </template>
    </c-form-v2>
  </div>
</template>
<style lang="scss">

</style>
<script lang="ts" setup>
import { storeToRefs } from 'pinia';

const { t } = useI18n();
const { request } = useApi();
const { maxCustomSmileysCount } = storeToRefs(useConfigStore());

const props = defineProps<{
  channel: Channels.Item;
}>();

useHead(() => ({
  title: t('dashboard.chat.heading')
}));

const customSmileysUploaderConfig = computed(() => ({
  title: t('dashboard.chat.custom_smileys.heading'),
  description: t('dashboard.chat.custom_smileys.description', { count: maxCustomSmileysCount.value }),
  folder: 'smileys',
  max: maxCustomSmileysCount.value,
  nameField: {
    use: true,
    title: t('dashboard.chat.custom_smileys.code'),
    id: 'code',
  }
}));

const save = (values: Partial<Channels.Item>) => {
  return request.put('/channels/:id', { body: values }, { id: props.channel.id });
}
</script>
