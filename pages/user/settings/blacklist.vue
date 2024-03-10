<template>
  <div class="blacklist">
    <c-box>
      <template slot="title">
        {{$t('blacklist.heading')}}
      </template>
      <template slot="main">
        <c-row>
          <c-col>
            <c-autocomplete v-model="form.data.username" :title="$t('dashboard.banlist.select_user')"  :errors="form.errors.username" autocomplete-key="id" autocomplete-value="username" url="users/autocomplete" />
          </c-col>
          <c-col with-button>
            <c-button :loading="form.loading" @click="banUser()" >{{$t('dashboard.banlist.add_user')}}</c-button>
          </c-col>
        </c-row>
      </template>
    </c-box>

    <c-list class="blacklist__users">
      <div class="centered" v-if="loading">
        <c-preloader  />
      </div>
      <c-nothing-found small v-else-if="blacklist.length === 0"></c-nothing-found>
      <c-list-item small :picture="item.user.avatar" v-for="item in blacklist" :key="item.user.id">
        <template slot="captions">
          <div class="list-item__title">{{item.user.username}}</div>
        </template>
        <template slot="buttons">
          <c-button @click="unblock(item)" color="red">{{$t('global.delete')}}</c-button>
        </template>
      </c-list-item>
    </c-list>
  </div>
</template>
<style lang="scss">
  .blacklist {
    padding: 0 2.5em;
    &__users {
      max-height: 50vh;
    }
  }
</style>
<script>
  export default {
    async mounted() {
      const blacklist = await this.$api.get('blacklist');
      this.loading = false;
      this.blacklist = blacklist;
    },
    data() {
      return {
        blacklist: [],
        loading: true,
        form: {
          loading: false,
          errors: {},
          data: {
            username: '',
          }
        }
      }
    },
    methods: {
      banUser() {
        this.form.loading = true;
        this.$api.post('blacklist', this.form.data).then(({item}) => {
          this.form.data.username = '';
          this.blacklist.unshift(item);
        }).catch(e => {
          this.errors = e.errors || [];
        }).finally(() => {
          this.form.loading = false;
        })
      },
      unblock(item) {
        this.$api.delete('blacklist/' + item.user.id).then(() => {
          this.blacklist = this.blacklist.filter(user => user.user.id !== item.user.id);
        })
      }
    }
  }
</script>
