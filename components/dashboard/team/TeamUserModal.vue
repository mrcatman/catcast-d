<template>
  <div>
    <c-autocomplete v-form-input="'username'" v-if="!editing" autocomplete-key="id" autocomplete-value="username" url="users/autocomplete"  :title="$t('global.enter_username')" />

    <c-radio-buttons v-if="data.permissions && !data.permissions.owner" v-form-input="'permissions.channel_admin'" :values="[
        {name: $t('dashboard.team.user_is_channel_admin'), value: 1},
        {name: $t('dashboard.team.select_individual_permissions'), value: 0},
      ]"  />

    <div v-if="data.permissions && (!data.permissions.owner && !data.permissions.channel_admin)" class="dashboard__team__permissions">
      <c-row v-for="permission in filteredPermissionsList" :key="permission.id" class="dashboard__team__permission">
        <c-col>
          <div class="dashboard__team__permission__title">{{$t(permission.title)}}</div>
          <div class="dashboard__team__permission__description">{{$t(permission.description)}}</div>
        </c-col>
        <c-col :grow="0" mobile-full-width>
          <c-radio-buttons class="dashboard__team__permission__buttons" :inline="true" :block="true" :values="getPermissionCheckboxValues(permission)" v-form-input="'permissions.' + permission.id" :default-value="0" />
        </c-col>
      </c-row>
      <div class="dashboard__team__permissions__text">
        {{$t('dashboard.team.full_access_text')}}
      </div>
    </div>
    <div class="vertical-delimiter"></div>
    <c-checkbox switch v-if="data.permissions && !data.permissions.owner" v-form-input="'hidden'" :title="$t('dashboard.team.hidden')" />
    <c-input v-if="!data.hidden" v-form-input="'position'" :title="$t('dashboard.team.position._title')" :description="$t('dashboard.team.position._description')" />
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
<script>
export default {
computed: {
  filteredPermissionsList() {
    return this.permissionsList.filter(item => item.id !== 'channel_admin' && item.can_be_added !== false);
  },
},
methods: {
  getPermissionCheckboxValues(permission) {
    const values = [
      {value: 0, name: this.$t('global.no')},
      {value: 1, name: permission.can_be_full ? this.$t('dashboard.team.permissions.limited') : this.$t('global.yes')},
    ]
    if (permission.can_be_full) {
      values.push({
        value: 2, name: this.$t('dashboard.team.permissions.full')
      })
    }
    return values;
  },
},
props: {
  data: Object,
  editing: Boolean,
  permissionsList: Array
}
}
</script>
