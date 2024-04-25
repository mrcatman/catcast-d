<template>
  <div class="user-online-mark">
    <div v-if="user.last_seen" >
      <div v-if="isOnline" class="user-online-mark__online">
        <span class="user-online-mark__online__mark"></span>
        <span class="user-online-mark__online__text">{{$t('profile.online')}}</span>
      </div>
      <div v-else class="user-online-mark__offline">
        <span class="user-online-mark__offline__text">{{$t('profile.was_online')}} </span>
        <span class="user-online-mark__offline__date">{{onlineTime}}</span>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .user-online-mark {
    margin-top: .25em;
    font-size: .9375em;
    &__online {
      &__mark {
        width: .5em;
        height: .5em;
        background: var(--green);
        display: inline-block;
        margin: 0 .5em .1em 0;
        border-radius: 50%;
      }
    }
  }
</style>
<script>
  import { format } from "date-fns";
  const now = new Date();
  export default {
    computed: {
      lastSeen() {
        return new Date(this.user.last_seen);
      },
      isOnline() {
        return now.getTime() - this.lastSeen.getTime() <= 300 * 1000;
      },
      onlineTime() {
        return format(this.lastSeen,"DD.MM.YYYY h:mm");
      }
    },
    props: {
      user: {
        type: Object,
        required: true,
      }
    },
  }
</script>
