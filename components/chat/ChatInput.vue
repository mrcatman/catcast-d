<template>
  <div class="chat__input-container">
    <div class="chat__input-container__outer">
      <div class="chat__reply-to" v-show="message.data.reply_to.length > 0">
        <a class="chat__reply-to__item" :key="$index" v-for="(item, $index) in message.data.reply_to">
          <c-button transparent icon-only narrow icon="close" @click="message.data.reply_to.splice($index, 1)" />
          <span :style="{'color': item.color}" class="chat__reply-to__item__username">
            {{item.username}},
          </span>
        </a>
      </div>
      <div class="chat__input-container__inner">
        <c-input ref="mainInput" :disabled="!loaded"  @keyup="onInputKeyup"  v-model="message.data.text" :errors="message.errors.text"></c-input>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.chat {
  &__reply-to {
    margin-left: .75em;
    &__item {
      margin-right: .75em;
      display: inline-flex;
      align-items: center;
      &__username {
        font-weight: bold;
        margin-left: .325em;
      }
    }
  }

  &__input-container {
    position: relative;
    display: flex;
    align-items: center;
    flex: 1;
    overflow: auto;
    &__inner {
      --input-bg-color: transparent;
      flex: 1;
      position: relative;
      --vertical-margin: 0;
    }

    &__outer {
      background: var(--input-bg-color);
      width: 100%;
      white-space: nowrap;
      display: flex;
      align-items: center;
    }
  }

  &__input {
    margin: 0 0 1em;
  }
}
</style>
<script>
import focus from "@/helpers/focus";

export default {
  methods: {
    onInputKeyup(e) {
      if (e.keyCode === 13) {
        this.$emit('sendMessage');
      }
      if (e.keyCode === 8) {
        if (this.message.data.text.length === 0 && this.message.data.reply_to.length > 0) {
          this.message.data.reply_to.splice(-1, 1);
        }
      }
    },
    focus() {
      const el = this.$refs.mainInput?.$refs?.input;
      if (!el) {
        return;
      }
      focus(el);
    },
  },
  data() {
    return {
      message: this.value
    }
  },
  watch: {
    message(message) {
      this.$emit('input', message);
    },
    value(message) {
      this.message = message;
    }
  },
  props: {
    loaded: {
      type: Boolean,
      required: false,
    },
    value: {
      type: Object,
      required: true
    }
  }
}
</script>
