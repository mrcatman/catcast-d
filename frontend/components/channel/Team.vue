<template>
  <div class="channel-layout__team">
    <c-preloader v-if="loading"  />
    <c-list v-else>
      <c-list-item :picture="member.avatar" :pictureSquare="true" :to="'/users/'+member.id" v-for="member in team" :key="member.id" >
        <template slot="captions">
          <div class="list-item__title">{{member.username}}</div>
          <div v-if="member.position" class="list-item__small-title">
            {{member.position}}
          </div>
        </template>
      </c-list-item>
    </c-list>
  </div>
</template>
<script>
  export default {
    async mounted() {
      this.team = await this.$api.get(`channels/${this.channel.id}/team`);
      this.loading = false;
    },
    data() {
      return {
        loading: true,
        team: [],
      }
    },
    props: {
      channel: {
        type: Object,
        required: true
      }
    },
  }
</script>
