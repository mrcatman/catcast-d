<template>
  <div v-if="user.last_seen" class="user-online-status">
    <div v-if="isOnline" class="user-online-status__online">
      <span class="user-online-status__online__dot"></span>
      <span class="user-online-status__online__text">{{ $t('profile.online') }}</span>
    </div>
    <div v-else class="user-online-status__offline">
      <span class="user-online-status__offline__text">{{ $t('profile.was_online') }}&nbsp;</span>
      <span class="user-online-status__offline__date">
          {{ wasOnline }}
          <c-tooltip position="bottom-center">{{ wasOnlineFull }}</c-tooltip>
        </span>
    </div>
  </div>
</template>
<style lang="scss">
.user-online-status {
  font-size: .9375em;

  &__online {
    &__dot {
      width: .5em;
      height: .5em;
      background: var(--green);
      display: inline-block;
      border-radius: 50%;
    }
  }
}
</style>
<script lang="ts" setup>
const {formatTimeAgo, formatDate} = useDates();

const props = defineProps<{
  user: Auth.User
}>();

const now = new Date();
const isOnline = computed(() => {
  if (!props.user.last_seen) {
    return false;
  }
  return now.getTime() - new Date(props.user.last_seen).getTime() <= 300 * 1000;
})

const wasOnline = computed(() => {
  return formatTimeAgo(props.user.last_seen);
})

const wasOnlineFull = computed(() => {
  return formatDate(props.user.last_seen);
})

</script>
