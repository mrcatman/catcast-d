<template>
  <c-box :link="`/users/${user.id}/friends`" v-if="friends.total > 0">
    <template slot="title">
      {{$t('profile.friends')}}
      <span class="box__header__count">{{friends.total}}</span>
    </template>
    <template slot="main">
      <c-dynamic-row :item-width="100" :rows="1" class="profile-page__friends">
        <template>
          <user-thumb :data="user" v-for="user in friends.data" :key="user.id"></user-thumb>
        </template>
      </c-dynamic-row>
    </template>
  </c-box>
</template>
<style lang="scss">
.profile-page {
  &__friends {
    height: 5em;
    overflow: hidden;
  }
}
</style>
<script>
import UserThumb from "@/components/thumbs/UserThumb";
export default {
  components: {UserThumb},
  props: {
    user: {
      type: Object,
      required: true,
    },
    friends: {
      type: Object,
      required: true,
    }
  }
}
</script>
