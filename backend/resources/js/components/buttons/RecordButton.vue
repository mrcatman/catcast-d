<template>
  <div class="subscribe-button" v-if="loggedIn">
    <c-button icon="email" @click="subscribeWindowVisible = true" color="green">
      <span>{{$t('notifications.subscribe')}}</span>
    </c-button>
    <c-modal :header="$t('notifications.heading')" v-model="subscribeWindowVisible">
      <div class="centered" v-if="loading">
        <c-preloader  />
      </div>
      <div v-else>
        <div class="input-container">
          <div class="input-container__title">{{$t('notifications.subtypes.heading')}}</div>
          <div class="row row--wrap">
            <c-checkbox class="checkbox--in-row" :key="subtype.name" v-for="subtype in subtypes" :title="$t(subtype.display_name)" v-model="subscription.subtypes[subtype.name]" />
          </div>
        </div>
        <div class="subscribe-button__panel">
          <c-button :loading="saving" @click="saveSubscription()">{{$t('notifications.subscribe_save')}}</c-button>
        <!--  <c-button v-if="subscription.id" :loading="isDeleting" @click="deleteSubscription()">{{$t('notifications.unsubscribe_button')}}</c-button> -->
        </div>
      </div>
    </c-modal>
  </div>
</template>
<style lang="scss">
.subscribe-button__panel {
  margin: 1em 0 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>
<script>
  import {mapState} from "vuex";

  export default {
    computed: {
      ...mapState('auth', ['loggedIn']),
    },
    props: {
      channel_id: {
        type: Number,
        required: true
      }
    },
    watch: {
      subscribeWindowVisible(isVisible) {
        if (isVisible && !this.dataLoaded) {
          this.loadData();
        }
      }
    },
    data() {
      return {
        loading: false,
        saving: false,
        isDeleting: false,
        dataLoaded: false,
        subscribeWindowVisible: false,
        subscription: {
          type: this.type,
          channels: {},
          subtypes: {},
        },
        subtypes: [],
      }
    },
    async mounted() {

    },
    methods: {
      saveSubscription() {
        this.saving = true;
        let postData = {
          type: this.type,
          entity_id: this.data.id,
          event_types: [],
        };
        Object.keys(this.subscription.subtypes).forEach(key=>{
          if (this.subscription.subtypes[key]) {
            postData.event_types.push(key);
          }
        });

        this.$axios.post('notifications/subscriptions', postData).then(res=>{
          this.$store.commit('NEW_ALERT', res.data);
          this.saving = false;
          if (res.data.status) {
            this.subscribeWindowVisible = false;
          }
        })
      },
      async loadData() {
        this.loading = true;
        let subscriptions = (await this.$api.get(`notifications/subscriptions?type=${this.type}&entity_id=${this.data.id}`));
        if (subscriptions.status) {
          let types = (await this.$api.get('notifications/bindings/gettypes'));
          this.subtypes = types.list[this.type].subtypes;
          if (subscriptions.list.length > 0) {
            let subtypes = {};
            subscriptions.list.forEach(subscription=>{
              subtypes[subscription.event_type] = true;
            });
            this.subscription.subtypes = subtypes;
          }
          this.dataLoaded = true;
          this.loading = false;
        } else {
          this.$store.commit('NEW_ALERT', subscriptions);
        }
      }
    }
  }
</script>
