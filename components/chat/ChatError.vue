<template>
  <div class="chat__error" v-if="state.loading_error || state.connect_error">
    <span class="chat__error__text" v-if="state.loading_error">{{$t('chat._errors.loading_error')}}</span>
    <span class="chat__error__text" v-else-if="state.connect_error">{{$t('chat._errors.connect_error')}}</span>
    <c-button flat @click="$emit('reconnect')">{{$t('chat.reconnect')}}</c-button>
  </div>
</template>
<style lang="scss" scoped>
.chat {
  &__error {
    padding: .5em 1em;
    background: var(--negative-color);
    color: var(--text-color);
    font-weight: 500;
    display: flex;
    align-items: center;
    &__text {
      font-size: .875em;
      margin-right: 1em;
      line-height: 1.5;
    }
  }
}
</style>
<script>
export default {
  props: {
    state: {
      type: Object,
      required: true
    }
  }
}
</script>
