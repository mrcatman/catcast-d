<template>
  <c-button :flat="!isSubscribed" :icon="isSubscribed ? 'fa-calendar-minus' : 'fa-calendar-plus'" @click="subscribe()" :loading="loading || loadingInitial" :count="count">{{!isSubscribed ? $t('timetable.panel.subscribe') : $t('timetable.panel.unsubscribe')}}</c-button>
</template>
<style lang="scss">

</style>
<script>
import { getDateWithYear } from '@/helpers/dates.js';
export default{
  computed: {
    itemType() {
      if (!this.data.item_type) {
        return 'announce';
      }
      return this.data.item_type;
    }
  },
	props: {
		data: {
			type: Object,
			required: true,
		},
	},
	data () {
		return {
		  loadingInitial: true,
			loading: false,
			count: 0,
      isSubscribed: false,
		}
	},
	async mounted() {
	  this.loadingInitial = true;
	  let data = (await this.$api.get(`timetable/subscription/${this.itemType}/${this.data.id}?day=${getDateWithYear(this.data.time)}`));
	  this.count = data.data.count;
	  this.isSubscribed = data.data.is_subscribed;
    this.loadingInitial = false;
	},
	methods: {
		subscribe() {
			if (this.$store.state.userData.loggedIn) {
				this.loading = true;
				this.$axios.post(`timetable/subscription/${this.itemType}/${this.data.id}`, {day: getDateWithYear(this.data.time), state: !this.isSubscribed}).then(res=>{
					this.loading = false;
					this.count = res.data.data.new_count;
					this.isSubscribed = res.data.data.is_subscribed;
				})
			} else{
				this.$router.push('/login');
			}
		}
	}
}
</script>
