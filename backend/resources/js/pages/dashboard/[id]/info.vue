<template>
<div ref="main">
  <c-form-v2 :initialValues="channel" :handler="save">
    <template #default="{ values, errors}">
      <c-box>
        <template #title>{{$t('dashboard.info.common.heading')}}</template>
        <template #main>
          <c-row>
            <c-col>
              <c-input v-model="values.name" :errors="errors.name" :title="$t('dashboard.info.common.name')"   />
            </c-col>
            <c-col>
              <c-input v-model="values.shortname" :errors="errors.shortname" :title="$t('dashboard.info.common.shortname')"  :prepend="`${siteDomain}/`"  />
            </c-col>
          </c-row>
          <c-text-editor v-model="values.description" :errors="errors.description" :title="$t('dashboard.info.common.description')"/>
          <c-tags-input v-model="values.tags" :errors="errors.tags" :title="$t('dashboard.info.common.tags')"/>
        </template>
      </c-box>



    </template>
  </c-form-v2>
  <div class="vertical-delimiter"></div>
  <c-box>
    <template #title>
      {{$t('dashboard.info.delete.heading')}}
    </template>
    <template #main>
      {{$t('dashboard.info.delete.text')}}
      <div class="vertical-delimiter"></div>
      <c-button icon="delete" color="red" @click="deleteChannel()">{{$t('dashboard.info.delete.button_text')}}</c-button>
    </template>
  </c-box>
</div>
</template>
<script lang="ts" setup>
import PrivacySettings from "@/components/dashboard/common/PrivacySettings.vue";

const props = defineProps<{
  channel: Channels.Item,
  permissions: any
}>();

const { siteDomain } = useConfigStore();

const { request } = useApi();
const save = (channel: Partial<Channels.Item>) => request.put('/channels/:id', {
  body: channel
}, {id: props.channel.id})

const deleteChannel = () => {

}
//
// export default {
//   components: {PrivacySettings},
//   head() {
//     return {
//       title: this.$t('dashboard.info.heading')
//     }
//   },
//   computed: {
//     ...mapGetters('config', ['siteDomain']),
//     canDeleteChannel() {
//       return this.permissions.owner || this.permissions.admin;
//     }
//   },
// 	props: {
//     permissions: {
//       type: Object,
//       required: true
//     },
// 		channel: {
// 			type: Object,
// 			required: true
// 		}
// 	},
//   methods: {
//     deleteChannel() {
//       this.$store.commit('modals/showStandardModal', {
//         confirm: true,
//         fn: async () => {
//           await this.$api.delete('channels/' + this.channel.id);
//           this.$router.push(`/dashboard`);
//         },
//       })
//     },
//   }
// }
</script>
