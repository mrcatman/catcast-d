<template>
  <c-box>
    <template slot="title">{{$t('settings.notifications')}}</template>
    <template slot="main">
      <c-form box :initialValues="bindings" method="put" url="notifications/bindings">
        <table class="notifications-table">
          <tbody>
          <template v-for="(category, $index) in categories">
            <tr class="notifications-table__type-name" :key="category.name">
              <td>{{ $t(category.category_name) }}</td>
            </tr>
            <tr v-for="event in category.events" :key="event.id">
              <td>{{ $t(event.name) }}</td>
              <td>
                <c-select multiple :options="channelOptions" v-form-input="event.id" />
              </td>
            </tr>
          </template>
          </tbody>
        </table>
      </c-form>

    </template>
  </c-box>
</template>
<script>
export default {
  computed: {
    channelOptions() {
      return this.channels.map(channel => {
        return {
          name: this.$t(channel.name),
          value: channel.id
        }
      })
    }
  },
  async asyncData({app}) {
    const categoriesList = await app.$api.get('notifications/events');
    const channels = await app.$api.get('notifications/channels');
    const bindings = await app.$api.get('notifications/bindings');

    const categories = {};
    categoriesList.forEach(category => {
      categories[category.category_name] = category;
      categories[category.category_name].events.forEach(event => {
        if (!bindings[event.event_type]) {
          bindings[event.event_type] = [];
        }
      })
    });

    return {
      bindings,
      channels,
      categories
    }
  },
  methods: {
    save() {
      this.saving = true;
      const bindings = [];
      Object.keys(this.bindings).forEach(key => {
        bindings.push({
          event_type: key,
          channels: Object.keys(this.bindings[key]).filter(channelKey => this.bindings[key][channelKey])
        })
      });
      this.$api.post('notifications/bindings', {bindings}, {notifyOnSuccess: true}).finally(() => {
        this.saving = false;
      })
    },
  },
  data() {
    return {
      saving: false,
    }
  }
}
</script>
<style lang="scss">
.notifications-table {
  width: 100%;
  margin-top: 1em;
  @media screen and (max-width: 768px) {
    font-size: .875em;
  }

  &__type-name {
    font-size: 1.25em;
    font-weight: 500;
    padding: .5em 0 0;
    width: 50%;
  }
}
</style>
