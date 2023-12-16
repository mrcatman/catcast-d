<template>
  <c-box>
    <template slot="main">
      <table class="notifications-table">
        <thead>
        <tr>
          <td></td>
          <td :key="channelName" v-for="(channel, channelName) in channels">{{ $t(channel.display_name) }}</td>
        </tr>
        </thead>
        <tbody>
        <template v-for="(type, $index) in types">
          <tr class="notifications-table__type-name" :key="$index">
            <td :colspan="Object.keys(channels).length + 1">{{ $t(type.display_name) }}</td>
          </tr>
          <tr :key="subtype.type_name" v-for="subtype in type.subtypes">

            <td>{{ $t(subtype.display_name) }}</td>
            <td :key="channel.name" v-for="(channel, channelName) in channels">
              <label class="notifications-table__checkbox-container">
                <c-checkbox v-model="bindings[subtype.type_name][channelName]" :disabled="!channels[channelName].can_subscribe" />
              </label>
            </td>
          </tr>
        </template>
        </tbody>
      </table>
    </template>
    <template slot="footer">
      <c-button :loading="saving" @click="save()">{{ $t('global.save') }}</c-button>
    </template>
  </c-box>
</template>
<script>
export default {
  async asyncData({app}) {
    const typesList = await app.$api.get('notifications/types');
    const channelsList = await app.$api.get('notifications/channels');
    const userBindings = await app.$api.get('notifications/bindings');

    const channels = {};
    channelsList.forEach(channel => {
      channels[channel.channel_name] = channel;
    });

    const types = {};
    const bindings = {};
    typesList.forEach(type => {
      types[type.type_name] = type;
      types[type.type_name].subtypes.forEach(subtype => {
        bindings[subtype.type_name] = {};
      })
    });
    userBindings.forEach(binding => {
      if (bindings[binding.event_type]) {
        bindings[binding.event_type][binding.notification_channel_type] = true;
      }
    });

    return {
      bindings,
      channels,
      types
    }
  },
  methods: {
    save() {
      this.saving = true;
      let bindings = [];
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
      data: {
        type: 'profile',
        channels: {},
        subtypes: {},
      },
    }
  }
}
</script>
<style lang="scss">
.notifications-table {
  width: 100%;
  margin: 0 0 .5em;
  @media screen and (max-width: 768px) {
    font-size: .875em;
  }

  &__type-name {
    font-size: 1.25em;
    font-weight: 500;
    padding: .5em 0 0;
    display: block;
  }

  thead td {
    text-align: center;
  }

  &__checkbox-container {
    cursor: pointer;
    display: flex;
    justify-content: center;
    width: 100%;
  }
}
</style>
