<template>
<div class="copy-tag">
  <m-input v-model="val" v-bind="$attrs" />
	<m-button @click="copy()" flat icononly icon="file_copy" class="copy-tag__button"></m-button>
</div>
</template>
<style lang="scss">
.copy-tag {
  display: flex;
  align-items: center;
	.input__element-container {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }
}
</style>
<script>
export default {
  methods: {
    copy() {
      const el = document.createElement('textarea');
      el.value = this.value;
      document.body.appendChild(el);
      el.select();
      document.execCommand('copy');
      document.body.removeChild(el);
    }
  },
  watch: {
    value(newValue) {
      this.val = newValue;
    }
  },
  data() {
    return {
      val: this.value
    }
  },
	props:{
		value: {
			type: String,
			required: false
		}
	}
}
</script>
