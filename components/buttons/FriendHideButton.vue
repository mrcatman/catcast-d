<template>
  <c-button :loading="loading" flat @click="submit()">
    {{$t('friends.hide_request')}}
  </c-button>
</template>
<script>
  import {mapMutations} from 'vuex';
  export default {
    computed: {},
    props: {
      id: {
        type: [String, Number],
        required: true
      },
    },
    data() {
      return {
        loading: false,
      }
    },
    methods: {
      ...mapMutations('friends', ['set']),
      submit() {
        this.loading = true;
        this.$axios.post('users/'+this.id+'/friends_request', {hide: true}).then(res=>{
          if (res.data.status) {
            this.$emit('success');
            this.set(res.data.data);
          }
        })
      }
    }
  }
</script>
