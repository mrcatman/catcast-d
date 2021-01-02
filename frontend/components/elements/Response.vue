<template>
<div v-if="data && data !== undefined && (data.status === 0 || data.status === 1)" class="response" :class="{'response--success':data.status == 1, 'response--fail':data.status == 0}">
	<span class="response__text">{{$t(data.text)}}</span>
</div>
</template>
<style lang="scss">
.response {
  font-weight: 500;
  font-size: .875em;
  padding: 1em;
  margin: 0 0 1em;
  text-align: left;
  /*border-radius: .5em; */
  &--success {
    background: rgba(24, 197, 30, 0.55);
    box-shadow: 0 0.25em 1em rgba(24, 197, 30, .55);
    border-left: 5px solid #24b724;
  }
  &--fail {
    background: rgba(197, 24, 44, 0.55);
    box-shadow: 0 0.25em 1em rgba(197, 24, 44, .55);
    border-left: 5px solid #ce1f37;
  }
}
</style>
<script>
export default{
	props: {
		data: {
			type:Object,
			required:false
		}
	}
}
</script>
