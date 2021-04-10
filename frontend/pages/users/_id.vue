<template>
  <div v-if="loaded" class="container">
    <UserInfo :user="user" />
    <m-box :no-padding="true">
      <template v-slot:heading>{{$t('users.teams._title')}}</template>
      <template v-slot:default>
        <m-list :cols="3">
          <m-list-item v-for="team in teams" :key="team.channel.id">
            <ChannelLink class="list-item__inner-link" :channel="team.channel">
              <m-list-item-picture v-if="team.channel.logo" :picture="team.channel.logo" />
              <m-list-item-texts>
                <m-list-item-title>
                  {{team.channel.name}}
                </m-list-item-title>
                <m-list-item-sub>
                  <span class="tag" v-if="team.owner">{{$t('users.teams.owner')}}</span>
                  <span class="tag" v-else-if="team.comment">{{team.comment}}</span>
                </m-list-item-sub>
              </m-list-item-texts>
            </ChannelLink>
          </m-list-item>

        </m-list>
      </template>
    </m-box>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import { UserGet, UserGetTeams } from '~/api/modules/users'
import User from '~/types/User'
import UserInfo from '~/components/layout/user/UserInfo.vue'
import ChannelLink from '~/components/layout/ChannelLink.vue';

@Component({
  components: { UserInfo, ChannelLink },
})
export default class UserPage extends Vue {
  user: User | null = null;
  teams: any = null;
  error = null;
  loaded: boolean = false

  async fetch() {
    try {
      this.user = await UserGet(this.$route.params.id);
      this.teams = await UserGetTeams(this.user.id);
      this.loaded = true;
    } catch (e) {
      console.log(e);
    }
  }
}
</script>
