<template>
<div v-if="data && data.message && data.message !== ''"
     class="response"
     :class="{'response--success': !data._has_errors,
      'response--fail': data._has_errors}"
  >
	<span class="response__text">{{$t(data.message)}}</span>
</div>
</template>
<style lang="scss">
.response {
  font-weight: 500;
  font-size: .875em;
  padding: 1em;
  text-align: left;
  color: var(--white);
  &--success {
    background: rgba(24, 197, 30, 0.55);
     border-left: .25em solid #24b724;
    .theme-modern & {
      box-shadow: 0 0.25em 1em rgba(24, 197, 30, .55);
    }
  }
  &--fail {
    background: rgba(197, 24, 44, 0.55);
    border-left: .25em solid #ce1f37;
    .theme-modern & {
      box-shadow: 0 0.25em 1em rgba(197, 24, 44, .55);
    }
  }
}
</style>
<script>
export default{
	props: {
		data: {
			type: Object,
			required: false
		}
	}
}
</script>
