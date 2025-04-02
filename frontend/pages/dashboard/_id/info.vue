<template>
<div ref="main">
  <c-form :initialValues="channel" method="put" :url="`/channels/${channel.id}`">
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

    <c-box>
      <template #title>
        {{$t('dashboard.info.common.links')}}
      </template>
      <template #main>
        <c-list-input v-model="values.links" :errors="errors.links" :fields="[{id: 'title', name: $t('links_editor.heading'), flexGrow: .5}, {id: 'url', name: $t('links_editor.url')}]" />

      </template>
    </c-box>

    // todo: channel layout (live/vod/etc)

    <c-box>
      <template #title>
        {{$t('dashboard.info.display.heading')}}
      </template>
      <template #main>
        <c-checkbox switch :title="$t('dashboard.info.display.show_in_autopilot_mode')" v-model="values.additional_settings.display.show_in_autopilot_mode" :errors="errors.additional_settings.display.show_in_autopilot_mode" />
        <c-checkbox switch :title="$t('dashboard.info.display.hide_autopilot_timetable')" v-model="values.additional_settings.display.hide_autopilot_timetable" :errors="errors.additional_settings.display.hide_autopilot_timetable" />
        <c-row centered>
          <c-col>
            <c-checkbox switch :title="$t('dashboard.info.display.protect_with_password')" v-model="values.additional_settings.display.protect_with_password" :errors="errors.additional_settings.display.protect_with_password"/>
          </c-col>
          <c-col v-form-show="'additional_settings.display.protect_with_password'">
            <c-input type="password" :title="$t('dashboard.info.display.watch_password')" v-model="values.additional_settings.display.watch_password" :errors="errors.additional_settings.display.watch_password" />
          </c-col>
        </c-row>
      </template>
    </c-box>
    <privacy-settings />
  </c-form>
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
<script>
import {mapGetters} from "vuex";
import PrivacySettings from "@/components/dashboard/common/PrivacySettings.vue";

export default {
  components: {PrivacySettings},
  head() {
    return {
      title: this.$t('dashboard.info.heading')
    }
  },
  computed: {
    ...mapGetters('config', ['siteDomain']),
    canDeleteChannel() {
      return this.permissions.owner || this.permissions.admin;
    }
  },
	props: {
    permissions: {
      type: Object,
      required: true
    },
		channel: {
			type: Object,
			required: true
		}
	},
  methods: {
    deleteChannel() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        fn: async () => {
          await this.$api.delete('channels/' + this.channel.id);
          this.$router.push(`/dashboard`);
        },
      })
    },
  }
}
</script>
