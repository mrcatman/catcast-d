<template>
  <div>
    <c-autocomplete
        v-if="!editing"
        v-model="values.user"
        :errors="errors.user"
        autocomplete-key="id"
        autocomplete-value="username"
        url="users/autocomplete"
        :title="$t('global.enter_username')"
    />

    <c-radio-buttons
        v-if="!isOwner"
        v-model="permissions.channel_admin"
        :values="[
          {name: $t('dashboard.team.user_is_channel_admin'), value: 1},
          {name: $t('dashboard.team.select_individual_permissions'), value: 0},
        ]"
    />

    <div v-if="!isOwner && !permissions.channel_admin" class="dashboard__team__permissions">
      <c-row v-for="permission in filteredPermissionsList" :key="permission.id" class="dashboard__team__permission">
        <c-col>
          <div class="dashboard__team__permission__title">{{ $t(permission.title) }}</div>
          <div class="dashboard__team__permission__description">{{ $t(permission.description) }}</div>
        </c-col>
        <c-col :grow="0" mobile-full-width>
          <c-radio-buttons
              class="dashboard__team__permission__buttons"
              inline
              block
              :values="getPermissionCheckboxValues(permission)"
              :default-value="0"
              v-model="permissions[permission.id]"
          />
        </c-col>
      </c-row>
      <div class="dashboard__team__permissions__text">
        {{ $t('dashboard.team.full_access_text') }}
      </div>
    </div>

    <div class="vertical-delimiter"></div>

    <c-checkbox
        v-if="!isOwner"
        is-switch
        v-model="values.hidden"
        :errors="errors.hidden"
        :title="$t('dashboard.team.hidden')"
    />
    <c-input
        v-if="!values.hidden"
        v-model="values.position"
        :errors="errors.position"
        :title="$t('dashboard.team.position.heading')"
        :description="$t('dashboard.team.position.description')"
    />
  </div>
</template>
<style lang="scss" scoped>
.dashboard__team {
  &__permissions {
    padding: var(--vertical-margin) 0;
    max-width: 48em;

    &__text {
      margin-top: var(--vertical-margin);
    }
  }

  &__permission {
    padding: var(--vertical-margin) 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);

    &:first-of-type {
      border-top: none;
      padding-top: 0;
    }

    &__title {
      font-size: 1.125em;
      font-weight: 600;
    }

    &__description {
      font-size: .875em;
      font-weight: 300;
    }

    &__buttons {
      font-size: .75em;
      white-space: nowrap;
    }
  }
}
</style>
<script lang="ts" setup>
const { t } = useI18n();

const props = withDefaults(defineProps<{
  values: Partial<Team.MemberBody>;
  errors?: Record<string, string[]>;
  editing?: boolean;
  permissionsList: Team.Permission[];
}>(), {
  errors: () => ({})
});

if (!props.values.permissions) {
  props.values.permissions = {};
}

const permissions = computed(() => props.values.permissions!);

const isOwner = computed(() => !!permissions.value.owner);

const filteredPermissionsList = computed(() => {
  return props.permissionsList.filter(item => item.id !== 'channel_admin' && item.can_be_added !== false);
});

const getPermissionCheckboxValues = (permission: Team.Permission) => {
  const values = [
    { value: 0, name: t('global.no') },
    { value: 1, name: permission.can_be_full ? t('dashboard.team.permissions.limited') : t('global.yes') },
  ];

  if (permission.can_be_full) {
    values.push({ value: 2, name: t('dashboard.team.permissions.full') });
  }

  return values;
}
</script>
