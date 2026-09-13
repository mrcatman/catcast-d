<template>
  <div>
    <c-box>
      <template #title>
        {{ $t('dashboard.broadcast.heading') }}
      </template>
      <template #main>
        <p v-html="!channel.is_radio ? $t('dashboard.broadcast.settings.description') : $t('dashboard.broadcast.settings.description_radio')"></p>

        <c-row>
          <c-col mobile-full-width>
            <c-copy-tag
                v-for="(server, $index) in servers"
                :key="$index"
                :title="$t('dashboard.broadcast.settings.rtmp_url')"
                :text="server.full_address"
            />
          </c-col>
          <c-col mobile-full-width>
            <c-row>
              <c-col>
                <c-copy-tag
                    password
                    ref="streamKey"
                    :title="!channel.is_radio ? $t('dashboard.broadcast.settings.rtmp_key') : $t('dashboard.broadcast.settings.radio_password')"
                    :text="key?.full_key"
                />
              </c-col>
              <c-col with-button>
                <c-button :loading="reloading" @click="generateNewKey()" flat>
                  {{ $t('dashboard.broadcast.settings.get_new_key') }}
                </c-button>
              </c-col>
            </c-row>
          </c-col>
        </c-row>
      </template>
    </c-box>

    <c-box>
      <template #title>
        {{ $t('dashboard.broadcast.active') }}
      </template>
      <template #main>
        <active-broadcast-display :channel="channel" :broadcast="activeBroadcast"/>
      </template>
    </c-box>

    <c-box>
      <template #title>{{ $t('dashboard.broadcast.recording.heading') }}</template>
      <template #main>
        <c-form-v2 :handler="saveRecordingSettings" :initial-values="channel">
          <template #default="{ values, errors }">
            <c-checkbox
                :title="$t('dashboard.broadcast.recording.record_all')"
                v-model="values.additional_settings.recording.record_all"
                :errors="errors['additional_settings.recording.record_all']"
            />
            <c-checkbox
                v-if="values.additional_settings.recording.record_all"
                :title="$t('dashboard.broadcast.recording.records_public')"
                v-model="values.additional_settings.recording.records_public"
                :errors="errors['additional_settings.recording.records_public']"
            />
          </template>
        </c-form-v2>
      </template>
    </c-box>
  </div>
</template>
<style lang="scss">

</style>
<script lang="ts" setup>
import ActiveBroadcastDisplay from "@/components/channel/ActiveBroadcastDisplay.vue";

const { t } = useI18n();
const { request } = useApi();
const { params } = useRoute();

const props = defineProps<{
  channel: Channels.Item;
}>();

useHead(() => ({
  title: t('dashboard.broadcast.heading')
}));

const streamKeyRef = useTemplateRef('streamKey');

const reloading = ref(false);

const { data } = await useAsyncData('broadcast-settings', async () => {
  const key = await request.get('/channels/:id/stream/key', {}, { id: params.id });
  const servers = await request.get('/channels/:id/stream/servers', {}, { id: params.id });
  const activeBroadcast = await request.get('/channels/:id/broadcasts/active', {}, { id: params.id });

  return { key, servers, activeBroadcast };
});

const key = ref<Broadcasts.StreamKey | undefined>(data.value?.key);
const servers = computed(() => data.value?.servers ?? []);
const activeBroadcast = computed(() => data.value?.activeBroadcast);

const generateNewKey = async () => {
  reloading.value = true;
  try {
    key.value = await request.get('/channels/:id/stream/key', {
      query: { generate_new_key: true }
    }, {
      id: props.channel.id
    });
    streamKeyRef.value?.show?.();
  } finally {
    reloading.value = false;
  }
}

const saveRecordingSettings = (values: Partial<Channels.Item>) => {
  return request.put('/channels/:id', { body: values }, { id: props.channel.id });
}
</script>
