<template>
  <div>
    <div v-for="permissionsCategory in permissionsSorted">
      <h3>{{permissionsCategory.name}}</h3>
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
            <m-button positive :loading="item._approving" icon="person_add">{{$t('permissions.approve')}}</m-button>
            <m-button negative :loading="item._rejecting" icon="person_remove">{{$t('permissions.reject')}}</m-button>
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
import { PermissionsGetMy } from '~/api/modules/permissions'
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
        list: this.permissions.filter(item => !item.approved && !item.rejected)
      },
      {
        name: this.$t('permissions.approved_list'),
        list: this.permissions.filter(item => item.approved)
      },
      {
        name: this.$t('permissions.rejected_list'),
        list: this.permissions.filter(item => item.rejected)
      }
    ]
  }

}
</script>
