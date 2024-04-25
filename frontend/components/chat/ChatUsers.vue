<template>
  <div class="chat__users">
    <div class="chat__users__group" :key="$index" v-for="(group, $index) in usersListSorted">
      <div class="chat__users__group__name">{{group.name}}</div>
      <div class="chat__users__list">
        <div class="chat__user" :class="{'chat__user--banned': user.is_banned}" :key="$index2" v-for="(user, $index2) in group.users">
          <div class="chat__user__info" @click="replyToUser(user)" >
            <c-icon icon="fa-user-tie" v-if="user.permissions.owner" class="chat__user__icon" />
            <c-icon icon="fa-crown" v-else-if="user.permissions.admin" class="chat__user__icon" />
            <a :style="{color: user.color}" class="chat__user__name">{{user.username}}</a>
          </div>
          <div class="chat__user__actions">
            <c-button transparent narrow icon-only icon="menu" >
              <c-popup-menu position="top-left" activate-on-parent-click>
                <c-popup-menu-item v-if="!user.is_guest" :to="`/users/${user.id}`" target="_blank">
                  {{ $t('chat.show_profile') }}
                </c-popup-menu-item>

                <c-popup-menu-item v-if="isTeamMember && user.is_guest && !user.is_banned" @click="parentComponent.changeGuestBanState(user, true)">{{$t('chat.ban')}}</c-popup-menu-item>
                <c-popup-menu-item v-if="isTeamMember && user.is_guest && user.is_banned" @click="parentComponent.changeGuestBanState(user, false)">{{$t('chat.unban')}}</c-popup-menu-item>
                <c-popup-menu-item v-if="isTeamMember && !user.is_guest && !user.is_banned && group.group_name === 'users'" @click="parentComponent.changeUserBanState(user, true)">{{$t('chat.ban')}}</c-popup-menu-item>
                <c-popup-menu-item v-if="isTeamMember && !user.is_guest && user.is_banned && group.group_name === 'users'" @click="parentComponent.changeUserBanState(user, false)">{{$t('chat.unban')}}</c-popup-menu-item>

              </c-popup-menu>
            </c-button>
         </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped lang="scss">
.chat {
  &__users {
    margin: 1em 0 -0.75em;
    &__group {
      margin-bottom: 1em;
      &:last-of-type {
        margin-bottom: 0;
      }
      &__name {
        font-size: 1.25em;
        font-weight: bold;
      }
    }
  }
  &__user {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: .25em 0;
    cursor: pointer;
    &__info {
      height: 1.25em;
      display: flex;
      &:hover {
        opacity: .75;
      }
    }
    &__icon + &__name {
      margin-left: .5em;
    }
    &__actions {
      margin-left: 1em;
    }
    &--banned &__name {
      opacity: .5;
      text-decoration: line-through;
    }
  }
}
</style>
<script>
export default {
  methods: {
    replyToUser(user) {
      this.parentComponent.replyToUser(user);
      this.$store.commit('modals/hideStandardModal');
    },

  },
  computed: {
    usersListSorted() {
      const users = JSON.parse(JSON.stringify(this.users)).sort((a, b) => b.is_banned - a.is_banned);
      let list = {
        team: [],
        users: [],
        guests: [],
      };
      let usersList = [];
      users.forEach( user => {
        user.is_me = this.me && this.me.id === user.id;
        if (user.permissions.owner || user.permissions.admin || user.permissions.moderation) {
          list.team.push(user);
        } else {
          if (user.is_guest) {
            if (this.config.allow_guests) {
              list.guests.push(user);
            }
          } else {
            list.users.push(user);
          }
        }
      });
      Object.keys(list).forEach(key=>{
        if (list[key].length > 0) {
          usersList.push({
            users: list[key],
            group_name: key,
            name: this.$t('chat.groups.' + key)
          })
        }
      });
      return usersList;
    }
  },
  props: {
    channel: {
      type: Object,
      required: true
    },
    config: {
      type: Object,
      required: true
    },
    me: {
      type: Object,
      required: true
    },
    users: {
      type: Array,
      required: true
    },
    isTeamMember: {
      type: Boolean,
      required: true
    },
    parentComponent: {
      type: Object,
      required: true
    }
  }
}
</script>
