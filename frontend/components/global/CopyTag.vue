<template>
<div class="copy-tag">
	<c-input ref="input" class="copy-tag__text" readonly :title="title" v-model="text" :type="password ? 'password' : 'text'">
    <template slot="buttons">
      <c-button @click="copy()" flat icon-only icon="file_copy" class="copy-tag__button"></c-button>
    </template>
  </c-input>
</div>
</template>
<style lang="scss">
.copy-tag {

  &__button {
    border-top-left-radius: 0!important;
    border-bottom-left-radius: 0!important;
    padding: .875em 1.5em;
    font-size: .5em;
  }
  &__text {
    margin: 0;
  }
}
</style>
<script>
export default {
  methods: {
    show() {
      this.$refs.input.showPassword = true;
    },
    copy() {
      const el = document.createElement('textarea');
      el.value = this.text;
      document.body.appendChild(el);
      el.select();
      document.execCommand('copy');
      document.body.removeChild(el);
    }
  },
	props: {
    title: String,
    password: Boolean,
		text: {
			type: String,
			required: true
		}
	}
}
</script>
