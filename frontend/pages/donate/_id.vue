<template>
  <div class="channel-layout__outer">
    <div class="channel-layout" v-if="!error" ref="page_outer">
      <div class="channel-layout__background" v-if="channel.background" :style="{background: 'url('+channel.background+') no-repeat center center', backgroundSize:'cover'}"></div>
      <div ref="banner_container" class="channel-layout__banner-container" v-if="channel.banner">
        <img class="channel-layout__banner" :src="channel.banner"/>
      </div>
      <div class="channel-layout__inner"  ref="page">
        <div class="box">
          <div class="channel-layout__donates-block">
            <div class="channel-layout__donates-block__info-container">
              <div class="channel-layout__donates" v-if="donates.donates_on">
                <div class="channel-layout__donates__top">
                  <div class="channel-layout__donates__header" v-if="donates.current_goal">{{donates.current_goal.title}}</div>
                  <div class="channel-layout__donates__header" v-else-if="donates.donates_settings.donate_comment && donates.donates_settings.donate_comment.length > 0">{{donates.donates_settings.donate_comment}}</div>
                  <div class="channel-layout__donates__header" v-else>{{$t('donates.default_header')}}</div>
                </div>
                <div class="channel-layout__donates__description" v-if="donates.current_goal">{{donates.current_goal.comment}}</div>
                <div class="channel-layout__donates__description" v-else>{{donates.donates_settings.donate_comment}}</div>
                <div class="channel-layout__donates__main" v-if="donates.current_goal">
                  <div class="channel-layout__donates__main__inner">
                    <div class="channel-layout__donates__collected-sum">{{donates.current_goal.sum_collected_readable}}</div>
                    <div class="channel-layout__donates__total-sum">{{donates.current_goal.sum_readable}}</div>
                    <div class="channel-layout__donates__progress" v-if="donates.current_goal">
                      <div class="channel-layout__donates__progress__bar" :style="{width: donates.current_goal.percent + '%'}"></div>
                    </div>
                  </div>
                </div>
                <div class="channel-layout__donates__list" v-if="donates.last_donates && donates.last_donates.length > 0">
                  <div class="list-item list-item--not-link" :key="$index" v-for="(donate, $index) in donates.last_donates">
                    <div class="list-item__left">
                      <div class="list-item__number">
                        {{donate.sum_readable}}
                      </div>
                      <div class="list-item__captions">
                        <div v-if="donate.user" class="list-item__title">{{donate.user.username}}</div>
                        <div class="list-item__under-title">{{donate.comment}}</div>
                      </div>
                    </div>
                    <div class="list-item__right">
                      <div class="dashboard-page__donates__time">{{donate.time_readable}}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="channel-layout__donates-block__form-container">
              <donatesForm :showHeader="true" @success="onDonateSuccess" :channel="channel" :data="donates" />
            </div>
          </div>
        </div>
      </div>
    </div>
    <c-error-page v-else :data="error" />
  </div>
</template>

<script>
  import donatesForm from '@/components/donatesForm';
  export default {
    layout: 'empty',
    components: {
      donatesForm
    },
    async asyncData({app, params, redirect}) {
      let channelData = (await app.$api.get(`/channels/${params.id}`));
      if (channelData.status) {
        let channel = channelData.data;
        let id = params.id;
        let donates = (await app.$api.get(`/donates/getbychannel/${channel.id}`)).data;
        if (!donates.donates_on) {
          return redirect('/'+channel.shortname);
        }
        return {
          error: null,
          donates,
          channel,
          id
        };
      } else {
        return {
          error: channelData
        };
      }
    },
    watch: {
    },
    data() {
      return {

      }
    },
    computed: {

    },
    mounted() {
      if (!this.error) {
        this.channel.colors.forEach((color, index) => {
          this.$refs.page_outer.style.setProperty(`--channel-colors-${index}`, color);
        });
      }
    },
    methods: {
      async onDonateSuccess() {
        this.donates = (await this.$api.get(`/donates/getbychannel/${this.channel.id}`)).data;
      },
    }
  }
</script>
