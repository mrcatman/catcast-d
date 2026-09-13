<template>
	<div class="radio-playback-switch-button">
		<c-button @click="switchRadioState()" :class="{'button--glow':isOnline === false, 'button--green':isOnline === false, 'button--red':isOnline === true}" :loading="loading">
      <span v-if="!isOnline">{{$t('radio_stream.start_station')}}</span>
      <span v-else>{{$t('radio_stream.stop_station')}}</span>
    </c-button>
	</div>
</template>
<style lang="scss">
.radio-playback-switch-button{
  display: inline-flex;
}
</style>
<script>
export default{
	props: {
    channel: {
      type: Object,
      required: true
    }
	},
	data:function(){
		  return {
		     isOnline: false,
			   loading: false,
		  }
	},
	created() {
			this.loading = true;
			this.$axios.get(`/radiostream/getstate/${this.channel.id}`).then(res=>{
			  this.loading = false;
        this.isOnline = res.data.data.is_online;
			})
	},
	methods: {
    switchRadioState() {
      this.loading = true;
      this.$axios.post(`/radiostream/setstate/${this.channel.id}`,{state: !this.isOnline}).then(res=>{
        this.loading = false;
        this.isOnline = res.data.is_online;
        if (!res.data.status) {
          this.$store.commit('NEW_ALERT',res.data);
        } else {
          this.$emit('switch', this.isOnline);
        }
      })
		}
	}
}
</script>
