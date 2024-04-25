<template>
<div class="centered-block">
	<div class="box box--no-padding announces-list">
    <div class="box__header">
			<div class="announces-list__header">
        <c-tabs v-show="$store.state.userData.loggedIn" toggle noChangeHash :data="filterVariants" v-model="filter" />
        <div class="announces-list__header__live-toggle" :class="{'announces-list__header__live-toggle--centered': $store.state.userData.loggedIn}">
          <c-checkbox :title="$t('announces.only_live')" v-model="onlyLive"/>
        </div>
        <c-datetime-picker :minDate="0" :dialogTitle="$t('announces.show_from_time')" :title="$t('announces.show_from_time')" v-model="time" />
      </div>
      </div>
      <div class="box__inner">
        <div  class="announces-list__inner">
        <c-preloader block  v-if="isReloading" />
        <c-nothing-found v-if="!loading && timetable.total === 0" :title="$t('global.nothing_found')" />
          <c-infinite-scroll v-else :loading="loading" @scroll="loadMore" class="announces-list__inner__scroll">
            <div class="announces-list__item" :key="$index" v-for="(item, $index) in timetable.data">
              <div class="announces-list__item__picture" v-if="item.picture" :style="'background: #111 url('+item.picture+') no-repeat center center; background-size: contain;'"></div>
              <div class="announces-list__item__info">
                <div class="announces-list__item__header">
                  <div class="announces-list__item__time">{{formatFullDate(item.time)}}</div>
                  <div class="announces-list__item__title">{{item.title}}</div>
                </div>
                <div class="announces-list__item__description" v-html="item.description"></div>
                <nuxt-link v-if="item.channel" :to="'/'+item.channel.shortname" class="announces-list__item__channel">
                  <div class="announces-list__item__channel__logo" :style="'background: url('+item.channel.logo+') no-repeat center center; background-size: contain;'"></div>
                  <div class="announces-list__item__channel__name">{{item.channel.name}}</div>
                </nuxt-link>
                <div v-if="item.time >= currentTs" class="announces-list__item__subscribe">
                  <AnnounceSubscribeButton :data="item"/>
                </div>
              </div>
            </div>
            <div v-show="!noNewItems" class="announces-list__load-more">
              <c-button @click="loadMore()">{{$t('announces.load_more')}}</c-button>
            </div>
          </c-infinite-scroll>
        </div>
      </div>
	</div>
</div>
</template>
<style lang="scss">
   .announces-list {
     min-width: 70vw;
     max-width: 80vw;
     max-height: 80vh;
     .box__header {
       background: var(--box-footer-color);
     }
     &__inner {
       position: relative;
       padding: 1em;
       min-height: 5em;
       &__scroll {
         overflow: auto;
         max-height: calc(80vh - 10em);
       }
     }
     &__header {
       color: var(--text-color);
       display: flex;
       align-items: center;
       justify-content: space-between;
       width: 100%;
       &__live-toggle {
         padding: 0 1em 0 0;
         &--centered {
           padding: 0;
           flex: 1;
           display: flex;
           align-items: center;
           justify-content: center;
         }
       }
     }
     &__load-more {
       padding: 1em 0 0;
       text-align: center;
     }
     &__item {
       display: flex;
       align-items: center;
       border-bottom: 1px solid rgba(255, 255, 255, .05);
       padding: .5em 0;
       position: relative;
       min-height: 2.5em;
       &__subscribe {
         position: absolute;
         top: 0;
         right: 1em;
         height: 100%;
         display: flex;
         align-items: center;
       }
       &__title {
        font-weight: 600;
       }
       &__description {
         font-size: .875em;
       }
       &__picture {
         background-color: #111;
         width: 7em;
         height: 4.5em;
         margin: 0 1em 0 0;
       }
       &__header {
         display: flex;
         align-items: flex-start;

       }

       &__time {
         font-weight: 600;
         margin: 0 .5em 0 0;
       }

       &__channel {
         margin: .75em 0 0;
         font-size: .875em;
         display: flex;
         align-items: center;
         text-decoration: none;
         &__logo {
           width: 2em;
           height: 2em;
           margin: 0 .5em 0 0;
         }
       }
     }
     @media screen and (max-width: 768px) {
       max-width: 100%;
       margin: 0 1em;
       max-height: 100%;
       height: calc(100vh - 7em);
       &__header {
         flex-direction: column;
       }
       &__item {
         flex-direction: column;
         text-align: center;
         &__subscribe {
           position: initial;
           justify-content: center;
         }

         &__header {
           flex-direction: column;
           align-items: center;
         }

         &__time {
           font-weight: 300;
           margin: .5em 0;
         }

         &__channel {
           justify-content: center;
           margin: .5em 0;
         }

         &__picture {
           width: 100%;
           height: 0;
           padding-top: 60%;
         }
       }
     }
   }
</style>
<script>
import { formatFullDate } from "@/helpers/dates";
import AnnounceSubscribeButton from '@/components/buttons/AnnounceSubscribeButton';
const onPage = 10;
export default {
  components: {
    AnnounceSubscribeButton
  },
  computed: {
    filterVariants() {
      return [
        {id: 'all', name: this.$t('announces.show_all')},
        {id: 'liked', name: this.$t('announces.show_only_from_favorite')},
      ];
    }
  },
	watch: {
    filter(newFilter) {
      this.$router.push({path: this.$route.path, query: Object.assign({}, this.$route.query, { filter: newFilter })});
      this.reloadSearch();
    },
    onlyLive(isLive) {
      this.$router.push({path: this.$route.path, query: Object.assign({}, this.$route.query, { onlyLive: isLive })});
      this.reloadSearch();
    },
    time(newTime) {
      this.internalTime = newTime;
      this.$router.push({path: this.$route.path, query: Object.assign({}, this.$route.query, { time: newTime })});
      this.reloadSearch();
    },
		currentPage() {

		}
	},
	data() {
		return {
		  currentTs: Math.ceil(new Date().getTime() / 1000),
		  noNewItems: false,
			loading: false,
      isReloading: false,
			currentPage: 1,
      time: this.$route.query.time || null,
      internalTime: this.$route.query.time || null,
      filter: this.$route.query.filter || 'all',
      onlyLive: (this.$route.query.onlyLive && this.$route.query.onlyLive  === 'true') || false,
      timetable: {},
		}
	},
	created() {

	},
  mounted() {
    this.load(true);
  },
	methods: {
    formatFullDate,
	  load(isReload) {
	    if (isReload || !this.noNewItems) {
        this.loading = true;
        let data = {
          page: this.currentPage,
          count: onPage,
          only_live: this.onlyLive,
          filter: this.filter
        };
        if (this.internalTime) {
          data.time = this.internalTime;
        }
        if (isReload) {
          this.isReloading = true;
          this.$set(this.timetable, 'data', []);
        }
        this.$axios.post(`/timetable`, data).then(res => {
          this.noNewItems = res.data.data.data.length === 0;
          if (isReload) {
            this.isReloading = false;
            this.timetable = res.data.data;
          } else {
            this.$set(this.timetable, 'data', [...this.timetable.data, ...res.data.data.data]);
          }
          this.loading = false;
        })
      }
    },
	  reloadSearch() {
      this.currentPage = 1;
      this.load(true);
    },
		loadMore() {
      if (!this.loading) {
        if (this.currentPage >= this.timetable.last_page) {
          this.currentPage = 1;
          this.internalTime = this.timetable.data[this.timetable.data.length - 1].time + 1;
          this.load();
        } else {
          this.currentPage++;
          this.load(false);
        }
      }
		}
	},
  head () {
    return {
      title: this.$t('announces.title'),
    }
  },
}
</script>
