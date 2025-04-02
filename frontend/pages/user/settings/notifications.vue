<template>
  <c-box>
    <template #title>{{ $t('settings.notifications') }}</template>
    <template #main>
      <c-form-v2 :handler="saveBindings" :initialValues="data.bindings">
        <template #default="{ values, errors }">
          <table class="notifications-table">
            <thead>
            <tr>
              <td>События</td>
              <td>Каналы</td>
            </tr>
            </thead>
            <tbody>
            <template v-for="category in data.categories" :key="category.name">
              <tr class="notifications-table__type-name">
                <td>{{ $t(category.category_name) }}</td>
              </tr>
              <tr v-for="event in category.events" :key="event.id">
                <td>{{ $t(event.name) }}</td>
                <td>
                  <c-select multiple :options="channelOptions" v-model="values[event.id]"/>
                </td>
              </tr>
            </template>
            </tbody>
          </table>
        </template>
      </c-form-v2>

    </template>
  </c-box>
</template>
<script lang="ts" setup>
const {request} = useApi();
const {t} = useI18n();

const {data, status} = await useAsyncData('user', async () => {
  const categoriesList = await request.get('/notifications/events');
  const channels = await request.get('/notifications/channels');
  const bindings = await request.get('/notifications/bindings');

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
});

const channelOptions = computed(() => {
  return data.value.channels.map(channel => {
    return {
      name: t(channel.name),
      value: channel.id
    }
  })
});

const saveBindings = (data) => {
  console.log(data);

  // const bindings = [];
  // Object.keys(this.bindings).forEach(key => {
  //   bindings.push({
  //     event_type: key,
  //     channels: Object.keys(this.bindings[key]).filter(channelKey => this.bindings[key][channelKey])
  //   })
  // });
  return request.put('notifications/bindings', {
    body: data
  })
}

</script>
<style lang="scss" scoped>
.notifications-table {
  width: 100%;
  margin-top: 1em;

  @media screen and (max-width: 768px) {
    font-size: .875em;
  }

  thead {
    font-size: 1.125em;
    font-weight: 500;

    td {
      padding-bottom: 1em;
    }
  }

  td {
    width: 50%;
  }



  &__type-name {
    font-size: 1.25em;
    font-weight: 500;
    padding-top: 2em;

    &:first-of-type {
      padding-top: 0;
    }
  }
}
</style>
