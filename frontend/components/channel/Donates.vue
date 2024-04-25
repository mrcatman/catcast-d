<template>
  <div class="channel-layout__box" v-if="donates.donates_on">
    <div class="channel-layout__donates" >
      <div class="channel-layout__donates__top">
        <div class="channel-layout__donates__header" v-if="donates.current_goal">{{donates.current_goal.title}}</div>
        <div class="channel-layout__donates__header" v-else-if="donates.donates_settings.donate_comment && donates.donates_settings.donate_comment.length > 0">{{donates.donates_settings.donate_comment}}</div>
        <div class="channel-layout__donates__header" v-else>{{$t('donates.default_header')}}</div>
        <c-button @click="donatesModalVisible = true" v-if="donates.current_goal && donates.current_goal.button_text && donates.current_goal.button_text.length > 0">{{donates.current_goal.button_text}}</c-button>
        <c-button @click="donatesModalVisible = true" v-else-if="donates.donates_settings & donates.donates_settings.button_text && donates.donates_settings.button_text.length > 0">{{donates.settings.button_text}}</c-button>
        <c-button @click="donatesModalVisible = true" v-else>{{$t('donates.default_button_text')}}</c-button>
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
  <c-modal class="donates-modal" v-model="donatesModalVisible" :header="$t('donates.modal_title')">
    <donatesForm @close="onDonateClose" @success="onDonateSuccess" :channel="channel" :data="donates" />
  </c-modal>
</template>
