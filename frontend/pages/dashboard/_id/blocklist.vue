<template>
  <div class="container page-form dashboard__blocklist">
    <h1 class="controls-page__heading">{{$t('dashboard.blocklist._title')}}</h1>
    <div class="controls-page__description">{{$t('dashboard.blocklist._description')}}</div>

    <div class="dashboard__blocklist__form">
          <div class="dashboard__blocklist__form__top">
            <m-autocomplete :placeholder="$t('dashboard.blocklist.search')" class="dashboard__blocklist__user-input" v-model="form.user" :fn="findUsers" :errors="errors.user" />
            <m-button class="dashboard__blocklist__form__add" :disabled="form.user.length === 0" @click="sendForm" :loading="formIsSubmitting">{{$t('common.add')}}</m-button>
          </div>
          <m-input :title="$t('dashboard.blocklist.comment')" v-model="form.comment" />
        </div>
        <m-list>
          <m-list-item v-for="blocked in blocklist" :key="blocked.id">
            <m-list-item-picture v-if="blocked.user.avatar" :picture="blocked.user.avatar" />
            <m-list-item-texts>
              <m-list-item-title>
                {{blocked.user.activitypub_handle}}
              </m-list-item-title>
              <m-list-item-sub v-if="blocked.comment">
                <strong>{{$t('dashboard.blocklist.comment')}}</strong> {{blocked.comment}}
              </m-list-item-sub>
              <m-list-item-sub v-if="blocked.blocked_by">
                <strong>{{$t('dashboard.blocklist.blocked_by')}}</strong> <UserLink :user="blocked.blocked_by">{{blocked.blocked_by.activitypub_handle}}</UserLink>
              </m-list-item-sub>
            </m-list-item-texts>
            <m-list-item-buttons>
              <m-button negative :loading="blocked._removing" @click="removeBlocked(blocked)" icon="person_remove">{{$t('common.delete')}}</m-button>
            </m-list-item-buttons>
          </m-list-item>
        </m-list>

  </div>
</template>
<style lang="scss">
.dashboard__blocklist {
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
  }
}
</style>
<script lang="ts">
import { Component } from 'nuxt-property-decorator'
import { ChannelAddUserToBlocklist, ChannelRemoveUserFromBlocklist, ChannelGetBlocklist } from '~/api/modules/channels'
import { Prop } from 'vue-property-decorator'
import Channel from '~/types/Channel'
import { UsersSearch } from '~/api/modules/users'
import { BaseFormComponent } from '~/components/types/BaseFormComponent'
import UserBan from '~/types/UserBan'
import UserLink from '~/components/layout/UserLink.vue'
@Component({
  components: { UserLink  },
})
export default class ChannelBlocklistPage extends BaseFormComponent {
  @Prop() readonly channel!: Channel

  blocklist: Array<any> = [];
  form = {
    user: '',
    comment: '',
  }

  get userIsLocal() {
    return this.form.user.indexOf('@') === -1;
  }

  async findUsers(query: string) {
    let users = await UsersSearch(query);
    return users.list.map(user => {
      return {
        id: user.activitypub_handle,
        name: user.activitypub_handle
      }
    })
  }

  async fetch() {
    this.blocklist = await ChannelGetBlocklist(this.channel.id!);
  }

  onSubmit(blocklistMember: UserBan) {
    this.blocklist = this.blocklist.filter(item => item.user.id !== blocklistMember.id);
    this.blocklist.push(blocklistMember);
  }

  submit() {
    let form = JSON.parse(JSON.stringify(this.form));
    return ChannelAddUserToBlocklist(this.channel.id!, form);
  }

  async removeBlocked(blocklistMember: UserBan) {
    this.$set(blocklistMember, '_removing', true);
    try {
      await ChannelRemoveUserFromBlocklist(this.channel.id!, blocklistMember.user.id!);
      this.blocklist.splice(this.blocklist.indexOf(blocklistMember), 1);
    } catch (e) {

    }
    this.$set(blocklistMember, '_removing', false);
  }
}
</script>
