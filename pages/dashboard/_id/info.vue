<template>
<div ref="main">
  <c-form :initialValues="channel" method="put" :url="`/channels/${channel.id}`">
    <c-box>
      <template slot="title">{{$t('dashboard.info.common._title')}}</template>
      <template slot="main">
        <c-row>
          <c-col>
            <c-input v-form-input="'name'" :title="$t('dashboard.info.common.name')"   />
          </c-col>
          <c-col>
            <c-input v-form-input="'shortname'" :title="$t('dashboard.info.common.shortname')"  :prepend="`${siteDomain}/`"  />
          </c-col>
        </c-row>
        <c-text-editor v-form-input="'description'" :title="$t('dashboard.info.common.description')"/>
        <c-tags-input v-form-input="'tags'" :title="$t('dashboard.info.common.tags')"/>
      </template>
    </c-box>

    <div class="vertical-delimiter"  ></div>

    <c-box>
      <template slot="title">
        {{$t('dashboard.info.common.links')}}
      </template>
      <template slot="main">
        <c-list-input v-form-input="'links'" :fields="[{id: 'title', name: $t('links_editor.title'), flexGrow: .5}, {id: 'url', name: $t('links_editor.url')}]" />

      </template>
    </c-box>

    // todo: channel layout (live/vod/etc)

    <div class="vertical-delimiter"></div>

    <c-box>
      <template slot="title">
        {{$t('dashboard.info.display._title')}}
      </template>
      <template slot="main">
        <c-checkbox switch :title="$t('dashboard.info.display.show_in_autopilot_mode')" v-form-input="'additional_settings.display.show_in_autopilot_mode'" />
        <c-checkbox switch :title="$t('dashboard.info.display.hide_autopilot_timetable')" v-form-input="'additional_settings.display.hide_autopilot_timetable'" />
        <c-row centered>
          <c-col>
            <c-checkbox switch :title="$t('dashboard.info.display.protect_with_password')" v-form-input="'additional_settings.display.protect_with_password'"/>
          </c-col>
          <c-col v-form-show="'additional_settings.display.protect_with_password'">
            <c-input type="password" :title="$t('dashboard.info.display.watch_password')" v-form-input="'additional_settings.display.watch_password'" />
          </c-col>
        </c-row>
      </template>
    </c-box>
    <privacy-settings />

    <c-box>
      <template slot="title">
        {{$t('dashboard.info.delete._title')}}
      </template>
      <template slot="main">
        {{$t('dashboard.info.delete.text')}}
        <div class="vertical-delimiter"></div>
        <c-button icon="delete" color="red" @click="deleteChannel()">{{$t('dashboard.info.delete.button_text')}}</c-button>
      </template>
    </c-box>
  </c-form>
</div>
</template>
<script>
import {mapGetters} from "vuex";
import PrivacySettings from "@/components/dashboard/common/PrivacySettings.vue";

export default {
  components: {PrivacySettings},
  head() {
    return {
      title: this.$t('dashboard.info._title')
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
