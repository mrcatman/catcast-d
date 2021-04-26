<template>
  <div class="container page-form dashboard__team">
    <h1 class="controls-page__heading">{{$t('dashboard.team._title')}}</h1>
        <div class="dashboard__team__form">
          <div class="dashboard__team__form__top">
            <m-autocomplete :placeholder="$t('dashboard.team.search')" class="dashboard__team__user-input" v-model="form.user" :fn="findUsers" :errors="errors.user" />
            <m-button class="dashboard__team__form__add" :disabled="form.user.length === 0" @click="sendForm" :loading="formIsSubmitting">{{$t('common.add')}}</m-button>
          </div>
          <div class="dashboard__team__form__full" v-show="userIsLocal">
            <m-checkbox v-model="form.full" :title="$t('permissions.full')"></m-checkbox>
          </div>
          <div class="dashboard__team__form__list" v-show="!form.full">
            <m-checkbox v-show="remoteAvailable.indexOf(permission) !== -1 || userIsLocal" v-for="permission in permissions" :key="permission" v-model="form.permissions[permission]" :title="$t('permissions.list.' + permission.toLowerCase())"></m-checkbox>
          </div>
          <m-input :title="$t('dashboard.team.comment')" v-model="form.comment" />
        </div>
        <m-list>
          <m-list-item v-if="owner">
            <m-list-item-picture v-if="owner.avatar" :picture="owner.avatar" />
            <m-list-item-texts>
              <m-list-item-title>
                {{owner.activitypub_handle}}
              </m-list-item-title>
              <m-list-item-sub>
                {{$t('dashboard.team.owner')}}
              </m-list-item-sub>
            </m-list-item-texts>
          </m-list-item>
          <m-list-item v-for="member in team" :key="member.id" :transparent="!member.confirmed">
            <m-list-item-picture v-if="member.user.avatar" :picture="member.user.avatar" />
            <m-list-item-texts>
              <m-list-item-title>
                {{member.user.activitypub_handle}}
              </m-list-item-title>

              <m-list-item-sub v-if="!member.confirmed">
                {{$t('dashboard.team.waiting_for_confirmation')}}
              </m-list-item-sub>
              <UserPermissionsView :permissions="member" />
            </m-list-item-texts>
            <m-list-item-buttons>
              <m-button negative :loading="member._removing" @click="removeMember(member)" icon="person_remove">{{$t('common.delete')}}</m-button>
            </m-list-item-buttons>
          </m-list-item>
        </m-list>

  </div>
</template>
<style lang="scss">
.dashboard__team {
  &__user-input {
    margin: 0 1em -.5em 0;
  }

  &__form {
    background: var(--box-color);
    padding: 1em;
    margin: 0 0 1em;
    padding: 1em 1em 1px;
    &__top {
      display: flex;
      align-items: flex-start;
    }

    &__add {
      margin: 2em 0 0;
    }

    &__full {
      border-bottom: 1px solid rgba(255, 255, 255, .1);
      padding: 0 0 .5em;
    }

    &__list {
      display: flex;
      flex-wrap: wrap;
      padding: 0 0 .5em;
      .checkbox {
        margin-right: 1.75em;
      }
    }
  }
}
</style>
<script lang="ts">
import { Component } from 'nuxt-property-decorator'
import { PermissionsGetList } from '~/api/modules/permissions'
import { ChannelAddUserToTeam, ChannelRemoveUserFromTeam, ChannelGetTeam } from '~/api/modules/channels'
import User from '~/types/User'
import { Prop } from 'vue-property-decorator'
import Channel from '~/types/Channel'
import { UsersSearch } from '~/api/modules/users'
import { BaseFormComponent } from '~/components/types/BaseFormComponent'
import UserPermissions from '~/types/UserPermissions'
import UserPermissionsView from '~/components/layout/UserPermissionsView.vue'
@Component({
  components: { UserPermissionsView },
})
export default class ChannelTeamPage extends BaseFormComponent {
  @Prop() readonly channel!: Channel

  permissions = [] as Array<String>;
  remoteAvailable = [] as Array<String>;
  owner: User | null = null;
  team: Array<any> = [];
  form = {
    user: '',
    comment: '',
    permissions: {},
    full: false
  }

  get userIsLocal() {
    if (this.form.user.indexOf('@') !== -1) {
      let domain = this.form.user.split('@')[1];
      return domain === this.$accessor.modules.site?.config?.domain;
    }
    return true;
  }

  async findUsers(query: string) {
    let users = await UsersSearch(query);
    return users.list.filter(user => {
      if (this.owner && this.owner.activitypub_handle === user.activitypub_handle) {
        return false;
      }
      return true;
    }).map(user => {
      return {
        id: user.activitypub_handle,
        name: user.activitypub_handle
      }
    })
  }

  async fetch() {
    let data = await PermissionsGetList();
    this.permissions = data.permissions;
    this.remoteAvailable = data.remote;
    let teamData = await ChannelGetTeam(this.channel.id!);
    this.owner = teamData.owner;
    this.team = teamData.team;
  }

  onSubmit(teamMember: UserPermissions) {
    this.team = this.team.filter(item => item.user.id !== teamMember.id);
    this.team.push(teamMember);
  }
  submit() {
    let form = JSON.parse(JSON.stringify(this.form));
    form.permissions = [];
    Object.keys(this.form.permissions).forEach(key => {
      if (this.form.permissions[key]) {
        form.permissions.push(key);
      }
    })
    return ChannelAddUserToTeam(this.channel.id!, form);
  }

  async removeMember(teamMember: UserPermissions) {
    this.$set(teamMember, '_removing', true);
    try {
      await ChannelRemoveUserFromTeam(this.channel.id!, teamMember.id!);
      this.team.splice(this.team.indexOf(teamMember), 1);
    } catch (e) {

    }
    this.$set(teamMember, '_removing', false);
  }
}
</script>
