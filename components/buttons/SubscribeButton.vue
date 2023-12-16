<template>
  <div class="subscribe-button" v-if="loggedIn">
    <c-button icon="subscriptions" @click="modalVisible = true" flat>
      <span>{{$t('notifications.settings')}}</span>
    </c-button>
    <c-modal :header="$t('notifications._title')" v-model="modalVisible">
      <template slot="main">
        <div class="centered" v-if="loading">
          <c-preloader  />
        </div>
        <div v-else>
           <c-checkbox :key="subtype.name" v-for="subtype in subtypes" :title="$t(subtype.display_name)" v-model="activeSubtypes[subtype.type_name]" />
        </div>
      </template>
      <template slot="buttons">
        <c-button :loading="saving" @click="saveSubscription()">{{$t('notifications.subscribe_save')}}</c-button>
      </template>
    </c-modal>
  </div>
</template>
<style lang="scss">
.subscribe-button {

}
</style>
<script>
  import {mapState} from "vuex";

  export default {
    props: {
      entityType: {
        type: String,
        required: true,
      },
      entityId: {
        type: Number,
        required: true,
      }
    },
    computed: {
      ...mapState('auth', ['loggedIn']),
    },
    watch: {
      modalVisible(isVisible) {
        if (isVisible && !this.dataLoaded) {
          this.loadData();
        }
      }
    },
    data() {
      return {
        loading: false,
        saving: false,
        dataLoaded: false,
        modalVisible: false,
        subtypes: [],
        activeSubtypes: {},
      }
    },
    methods: {
      saveSubscription() {
        this.saving = true;
        const eventTypes = [];
        this.subtypes.forEach(subtype => {
          if (this.activeSubtypes[subtype.type_name]) {
            eventTypes.push(subtype.type_name);
          }
        });

        this.$api.post(`notifications/subscriptions/${this.entityType}/${this.entityId}`, {
          event_types: eventTypes
        }).then(res => {
          if (!res._has_errors) {
            this.modalVisible = false;
          }
        }).finally(() => {
          this.saving = false;
        })
      },
      async loadData() {
        this.loading = true;
        const subscriptions = await this.$api.get(`notifications/subscriptions/${this.entityType}/${this.entityId}`);
        const types = (await this.$api.get('notifications/types'));
        this.subtypes = types.filter(type => type.type_name === this.entityType)[0]?.subtypes || [];
        if (subscriptions.length > 0) {
          subscriptions.forEach(subscription => {
            this.$set(this.activeSubtypes, subscription.event_type, true);
          });
        }
        this.dataLoaded = true;
        this.loading = false;
      }
    }
  }
</script>
