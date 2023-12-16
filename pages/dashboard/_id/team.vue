<template>
  <c-box no-padding class="dashboard-page__team">
    <template slot="main">
      <c-thumbs-list ref="list" :config="listConfig">
        <template slot="before_filters">
          <c-button color="green" icon="person_add" @click="addMember()">{{$t('dashboard.team.add')}}</c-button>
        </template>
        <template slot="item" slot-scope="props">
          <c-list-item :not-confirmed="!props.item.confirmed" :picture="props.item.user.avatar" picture-square>
            <template slot="captions">
              <div class="list-item__title">
                {{props.item.user.username}}
              </div>
              <span class="list-item__title__sub">
                <span class="dashboard__team__user__position" v-if="props.item.position">{{props.item.position}}</span>
                <span class="dashboard__team__user__waiting-for-confirmation" v-if="!props.item.confirmed">{{$t('dashboard.team.waiting_for_confirmation')}}</span>
              </span>
              <div class="list-item__under-title">
                <c-tag :key="$index" v-for="(key, $index) in getShortPermissionsList(props.item)">{{key}}</c-tag>
              </div>
            </template>
            <template slot="buttons">
              <div class="buttons-row">
                <c-button @click="editMember(props.item)" v-if="props.item.can_edit" color="green">{{$t('global.edit')}}</c-button>
                <c-button @click="deleteUser(props.item)" v-if="props.item.can_delete" color="red">{{$t('global.delete')}}</c-button>
              </div>
            </template>
          </c-list-item>
        </template>
      </c-thumbs-list>
    </template>
  </c-box>
</template>
<style lang="scss">
.dashboard {
  &__team {
    &__user {
      &__position {
        margin: 0 .5em 0 0;
      }

      &__waiting-for-confirmation {
        border-bottom: 1px dashed;
      }
    }
    &__users-list {
      position: relative;
      background: var(--box-element-color);
      flex: 2.5;
    }
    &__add-user {
      display: flex;
      flex-direction: column;
      justify-content: center;
      &__inputs {
        display: flex;
        flex-direction: column;
      }
      &__permissions {
        &__list {
          margin: 1em 0;
          &--edit {
            margin: 1em 0 2.5em;
          }
          &--inactive {
            opacity: .25;
          }
        }
      }
    }
  }
}

</style>
<script>
import TeamUserModal from "@/components/dashboard/team/TeamUserModal.vue";

export default {
  watch: {
  },
  head() {
    return {
      title: this.$t('dashboard.team._title')
    }
  },
  async asyncData({ app }) {
    const permissionsList = await app.$api.get(`permissions`);
    return { permissionsList };
  },
  props: {
    channel: {
      type: Object,
      required: true
    }
  },
  computed: {
    fullPermissionsValues() {
      return [
        {id: true, title: this.$t('dashboard.team.user_is_channel_admin')},
        {id: false, title: this.$t('dashboard.team.select_individual_permissions')},
      ]
    },
    listConfig() {
      return {
        view: 'list',
        title: this.$t('dashboard.team._title'),
        url: `/channels/${this.channel.id}/team/manager`,
        paginate: true,
        infiniteScroll: true,
        search: true,
        innerScroll: true,
        noPadding: true
      }
    }
  },
  data() {
    return {
      form: {
        editing: false,
        username: '',
        position: '',
        isChannelAdmin: true,
        permissions: {},
      },
      loading: false,
      userToAdd: null,
    }
  },

  methods: {
    getShortPermissionsList(member) {
      const list = Object.keys(member.permissions);
      const maxLength = 5;
      const isLong = list.length > maxLength;
      const shortList = (isLong ? list.slice(0, maxLength) : list).map(key => {
        const permission = this.permissionsList.filter(item => item.id === key)[0];
        return this.$t(permission.title);
      });
      if (isLong) {
        shortList.push('+ ' + (list.length - maxLength));
      }
      return shortList;
    },
    addMember() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        component: TeamUserModal,
        props: {
          editing: false,
          permissionsList: this.permissionsList,
        },
        formValues: {
          permissions: {
            channel_admin: 1
          }
        },
        title: this.$t('dashboard.team.add'),
        buttonColor: '',
        buttonText: this.$t('global.add'),
        fn: async (values) => {
          await this.$api.post(`/channels/${this.channel.id}/team/`, values);
          this.$refs.list.load();
        },
      })
    },
    editMember(member) {
      if (!member.permissions.channel_admin) {
        member.permissions.channel_admin = 0;
      }
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        component: TeamUserModal,
        props: {
          editing: true,
          permissionsList: this.permissionsList,
        },
        formValues: {
          ...member,
        },
        title: this.$t('dashboard.team.edit', {username: member.user.username}),
        buttonColor: '',
        buttonText: this.$t('global.save'),
        fn: async (values) => {
          await this.$api.post(`/channels/${this.channel.id}/team/`, values);
          this.$refs.list.load();
        },
      })
    },
    deleteUser(member) {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        title: this.$t('dashboard.team.delete._title'),
        text: this.$t('dashboard.team.delete._text'),
        fn: async () => {
          await this.$api.delete(`/channels/${this.channel.id}/team/${member.id}`);
          this.$refs.list.load();
        },
      })
    }
  }
}
</script>
