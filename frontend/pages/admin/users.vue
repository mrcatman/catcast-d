<template>
 <div class="admin-panel-page__users">
   <m-radio-buttons :inline="true" :values="usersListsTypes" v-model="usersListType" title="" />
   <m-table :fn="getFunction" :config="tableConfig">
     <template v-slot:after_row="{ item }">
       <m-select @change="setRole(item)"  v-if="!isMe(item)"placeholder="" :options="rolesOptions" v-model="item.role_id" />
       <span v-if="!isMe(item)" class="horizontal-delimiter"></span>
       <m-button @click="toggleBan(item)" v-if="!isMe(item)">{{!item.is_blocked ? $t('admin.users.block') : $t('admin.users.unblock')}}</m-button>
     </template>
   </m-table>
  </div>
</template>
<style lang="scss">
  .admin-panel-page--users {
    width: 100%;
    max-width: unset;
  }
</style>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import { AdminGetUsersAll, AdminGetUsersLocal, AdminGetUsersRemote, AdminUpdateUser } from '~/api/modules/admin'
import User from '~/types/User'
import {Role} from '~/types/roles'
@Component({})

export default class AdminSettingsUsers extends Vue {

  usersListType = 'all';
  usersListsTypes =  [
    {id: 'all', title: this.$t('admin.users.list_types.all'), fn: AdminGetUsersAll},
    {id: 'local', title: this.$t('admin.users.list_types.local'), fn: AdminGetUsersLocal},
    {id: 'remote', title: this.$t('admin.users.list_types.remote'), fn: AdminGetUsersRemote},
  ];

  get getFunction() {
    return this.usersListsTypes.filter(type => type.id === this.usersListType)[0].fn;
  }

  get rolesOptions() {
    let roles = [];
    for (let item in Role) {
      if (!isNaN(Number(item))) {
        roles.push({
          id: item,
          title: this.$t('roles.role_' + item),
        })
      }
    }
    return roles;
  }

  tableConfig = {
    columns: [
      {
        prop: 'id',
        name: this.$t('admin.users.id'),
      },
      {
        prop: 'login',
        name: this.$t('admin.users.login'),
        link: (item: User) => {
          return item.domain ? `/users/${item.id}` : `/users/${item.login}`;
        }
      },
      {
        prop: 'domain',
        name: this.$t('admin.users.domain'),
      },
      {
        prop: 'email',
        name: this.$t('admin.users.email'),
      },
      {
        prop: 'last_time_seen',
        name: this.$t('admin.users.last_time_seen'),
        fn: (item: User) => {
          return new Date(item.last_time_seen).toLocaleString();
        }
      },
    ],
    getClasses(item: User) {
      return [
        item.is_blocked ? 'table__row--strike-out' : ''
      ];
    }
  }

  isMe(user: User) {
    return  this.$accessor.modules.auth.me.id === user.id;
  }

  setRole(user: User) {
    AdminUpdateUser(user.id, {role_id: user.role_id})
  }

  async toggleBan(user: User) {
    await AdminUpdateUser(user.id, {is_blocked: !user.is_blocked})
    user.is_blocked = !user.is_blocked;
  }

}
</script>
