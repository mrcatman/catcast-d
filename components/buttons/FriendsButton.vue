<template>
  <c-button v-if="currentState !== 'STATE_SELF'" :loading="loading" color="green" :flat="buttonFlat" @click="submit()">
    {{ buttonText }}
  </c-button>
</template>
<script>
  import {mapMutations} from 'vuex';
  export default {
    computed: {
      buttonFlat() {
        let state = this.currentState;
        return (state === "STATE_IN_FRIENDS" || state === "STATE_SENT_REQUEST");
      },
      buttonText() {
        let state = this.currentState;
        if (state === "STATE_NOT_IN_FRIENDS") {
          return this.$t('friends.add');
        }
        if (state === "STATE_IN_FRIENDS") {
          return this.$t('friends.remove');
        }
        if (state === "STATE_SENT_REQUEST") {
          return this.$t('friends.remove_request');
        }
        if (state === "STATE_RECEIVED_REQUEST") {
          return this.$t('friends.accept_request');
        }
        return "";
      }
    },
    props: {
      id: {
        type: Number,
        required: true
      },
      state: {
        type: String,
        required: true,
      }
    },
    data() {
      return {
        loading: false,
        currentState: this.state,
      }
    },
    methods: {
      ...mapMutations('friends', ['set']),
      submit() {
        this.loading = true;
        const state = ["STATE_RECEIVED_REQUEST", "STATE_NOT_IN_FRIENDS"].indexOf(this.currentState) !== -1;
        this.$api.post(`/users/${this.id}/friends`, {state}).then(({state}) => {
          this.$emit('changeState', state);
          this.currentState = state;
        }).finally(() => {
          this.loading = false;
        })
      }
    }
  }
</script>
