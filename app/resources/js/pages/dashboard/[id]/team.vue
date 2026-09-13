<template>
  <c-box no-padding class="dashboard-page__team">
    <template #main>
      <c-thumbs-list ref="list" :config="listConfig">
        <template #before_filters>
          <c-button color="green" icon="person_add" @click="addMember()">{{ $t('dashboard.team.add') }}</c-button>
        </template>
        <template #item="{ item }">
          <c-list-item :not-confirmed="!item.confirmed" :picture="item.user.avatar" picture-square>
            <template #captions>
              <div class="list-item__title">
                {{ item.user.username }}
              </div>

              <div class="list-item__under-title">
                <span class="dashboard__team__user__position" v-if="item.position">{{ item.position }}</span>
                <span class="dashboard__team__user__waiting-for-confirmation" v-if="!item.confirmed">
                  {{ $t('dashboard.team.waiting_for_confirmation') }}
                </span>
                <c-tag :key="$index" v-for="(key, $index) in getShortPermissionsList(item)">{{ key }}</c-tag>
              </div>
            </template>
            <template #buttons>
              <div class="buttons-row">
                <c-button @click="editMember(item)" v-if="item.can_edit" color="green">{{ $t('global.edit') }}</c-button>
                <c-button @click="deleteMember(item)" v-if="item.can_delete" color="red">{{ $t('global.delete') }}</c-button>
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
<script lang="ts" setup>
import TeamUserModal from "@/components/dashboard/team/TeamUserModal.vue";
import { type ListConfig } from '@/composables/usePaginatedData';

const { t } = useI18n();
const { request } = useApi();
const { showModal } = useModal();

const props = defineProps<{
  channel: Channels.Item;
}>();

useHead(() => ({
  title: t('dashboard.team.heading')
}));

const listRef = useTemplateRef('list');

const { data: permissionsList } = await useAsyncData('permissions', () => request.get('permissions'), {
  default: () => [] as Team.Permission[]
});

const listConfig = computed<ListConfig<Team.Member>>(() => ({
  view: 'list',
  title: t('dashboard.team.heading'),
  infiniteScroll: true,
  search: true,
  innerScroll: true,
  noPadding: true,
  handler: (params) => request.get('/channels/:id/team/manager', {
    query: params
  }, {
    id: props.channel.id
  })
}));

const getShortPermissionsList = (member: Team.Member) => {
  const list = Object.keys(member.permissions);
  const maxLength = 5;
  const isLong = list.length > maxLength;

  const shortList = (isLong ? list.slice(0, maxLength) : list).map(key => {
    const permission = permissionsList.value.find(item => item.id === key);
    return permission ? t(permission.title) : key;
  });

  if (isLong) {
    shortList.push('+ ' + (list.length - maxLength));
  }

  return shortList;
}

const isUserSelected = (values: Partial<Team.MemberBody>) => {
  return !!values.user && values.user.username.length > 0;
}

const saveMember = async (values: Team.MemberBody) => {
  await request.post('/channels/:id/team', { body: values }, { id: props.channel.id });
  listRef.value?.load();
}

const addMember = () => {
  showModal<Team.MemberBody>({
    confirm: true,
    component: TeamUserModal,
    props: {
      editing: false,
      permissionsList: permissionsList.value,
    },
    formValues: {
      permissions: {
        channel_admin: 1
      }
    },
    title: t('dashboard.team.add'),
    buttonColor: '',
    buttonText: t('global.add'),
    buttonDisabledFn: (values) => !isUserSelected(values),
    fn: saveMember,
  })
}

const editMember = (member: Team.Member) => {
  showModal<Team.MemberBody>({
    confirm: true,
    component: TeamUserModal,
    props: {
      editing: true,
      permissionsList: permissionsList.value,
    },
    formValues: {
      ...member,
      permissions: {
        channel_admin: 0,
        ...member.permissions,
      }
    },
    title: t('dashboard.team.edit', { username: member.user.username }),
    buttonColor: '',
    buttonText: t('global.save'),
    buttonDisabledFn: (values) => !isUserSelected(values),
    fn: saveMember,
  })
}

const deleteMember = (member: Team.Member) => {
  showModal({
    confirm: true,
    title: t('dashboard.team.delete.heading'),
    text: t('dashboard.team.delete.text'),
    fn: async () => {
      await request.delete('/channels/:id/team/:userId', {}, {
        id: props.channel.id,
        userId: member.id
      });
      listRef.value?.load();
    },
  })
}
</script>
