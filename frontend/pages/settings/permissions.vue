<template>
  <div>
    <div v-for="permissionsCategory in permissionsSorted">
      <h3 class="controls-page__subheading">{{permissionsCategory.name}}</h3>
      <m-list>
        <m-list-item v-for="item in permissionsCategory.list" :key="item.id">
          <m-list-item-picture v-if="item.channel.logo" :picture="item.channel.logo" />
          <m-list-item-texts>
            <m-list-item-title>
              {{item.channel.name}}
            </m-list-item-title>
            <m-list-item-sub>
              {{item.channel.activitypub_handle}}
            </m-list-item-sub>
            <UserPermissionsView :permissions="item" />
            <m-button v-if="!item.confirmed && !item.rejected" @click="confirm(item)" positive :loading="item.confirming" icon="person_add">{{$t('permissions.confirm')}}</m-button>
            <m-button v-if="!item.confirmed && !item.rejected" @click="reject(item)" negative :loading="item._rejecting" icon="person_remove">{{$t('permissions.reject')}}</m-button>
          </m-list-item-texts>
        </m-list-item>
      </m-list>
    </div>
  </div>
</template>
<script lang="ts">
import { Component } from 'nuxt-property-decorator'
import { BaseFormComponent } from '~/components/types/BaseFormComponent'
import UserPermissions from '~/types/UserPermissions'
import { PermissionsConfirm, PermissionsGetMy, PermissionsReject } from '~/api/modules/permissions'
import UserPermissionsView from '~/components/layout/UserPermissionsView.vue'
@Component({
  components: { UserPermissionsView },
})
export default class ChannelTeamPage extends BaseFormComponent {
  permissions = [] as Array<UserPermissions>;

  async fetch() {
    this.permissions = await PermissionsGetMy();
  }

  get permissionsSorted() {
    return [
      {
        name: this.$t('permissions.requests_list'),
        list: this.permissions.filter(item => !item.confirmed && !item.rejected)
      },
      {
        name: this.$t('permissions.confirmed_list'),
        list: this.permissions.filter(item => item.confirmed)
      },
      {
        name: this.$t('permissions.rejected_list'),
        list: this.permissions.filter(item => item.rejected)
      }
    ]
  }

  async confirm(teamMember: UserPermissions) {
    this.$set(teamMember, '_confirming', true);
    try {
      await PermissionsConfirm(teamMember.id!);
      this.$set(teamMember, 'confirmed', true);
    } catch (e) {}
    this.$set(teamMember, '_confirming', false);
  }

  async reject(teamMember: UserPermissions) {
    this.$set(teamMember, '_rejecting', true);
    try {
      await PermissionsReject(teamMember.id!);
      this.$set(teamMember, 'rejected', true);
    } catch (e) {}
    this.$set(teamMember, '_rejecting', false);
  }

}
</script>
