<template>
  <div class="dashboard__subscribers">
    <c-box no-padding>
      <template slot="title">
        {{ $t('dashboard.subscribers.heading') }}
      </template>
      <template slot="main">
        <c-thumbs-list ref="users_list" :config="listConfig">
          <template slot="item" slot-scope="props">
            <c-list-item :to="`/users/${props.item.user.id}`" :picture="props.item.user.avatar" :picture-square="true">
              <template slot="captions">
                <a class="list-item__title">{{ props.item.user.username }}</a>
                <div class="list-item__under-title">
                  <span>{{
                      $t('dashboard.subscribers.subscribed_at', {date: formatFullDate(props.item.created_at)})
                    }}</span>
                </div>
              </template>
            </c-list-item>
          </template>
        </c-thumbs-list>
      </template>
    </c-box>
  </div>
</template>
<style lang="scss">
.dashboard__subscribers {

}
</style>
<script>
import { formatFullDate } from '@/helpers/dates';

const baseListConfig = {
  view: 'list',
  paginate: true,
  infiniteScroll: true,
  search: true,
  innerScroll: true,
  noPadding: true
};

export default {
  head() {
    return {
      title: this.$t('dashboard.subscribers.heading')
    }
  },
  props: {
    channel: {
      type: Object,
      required: true
    }
  },

  computed: {
    listConfig() {
      return {
        ...baseListConfig,
        url: `/channels/${this.channel.id}/subscribers`,
      }
    },
  },
  data() {
    return {
      loading: false,
    }
  },
  methods: {
    formatFullDate,
  }
}
</script>
