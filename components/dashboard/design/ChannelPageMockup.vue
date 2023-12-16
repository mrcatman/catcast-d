<template>
  <div class="page-mockup">
    <div class="page-mockup__main" :style="{backgroundImage: 'url(' +(picturesData.background ? picturesData.background.full_url : '')+')'}">
      <div class="page-mockup__main__channel-banner" v-if="picturesData.banner && picturesData.banner.full_url"
           :style="{backgroundImage: 'url(' +picturesData.banner.full_url+')' }">
      </div>
      <div class="page-mockup__main__box">
        <div class="page-mockup__main__header">
          <div class="page-mockup__main__channel-logo" v-if="picturesData.logo && picturesData.logo.full_url" :style="{backgroundImage: 'url(' +picturesData.logo.full_url+')' }"></div>
          <div class="page-mockup__main__channel-name">{{channel.name}}</div>
        </div>
        <div class="page-mockup__main__channel-description">
          <span v-if="channel.description && channel.description.length > 0" v-html="channel.description"></span>
          <span v-else v-html="mockupText"></span>
        </div>
        <div class="page-mockup__main__bottom">
          <c-button class="page-mockup__main__button">{{$t('dashboard.design.page.button')}}</c-button>
          <a href="#" class="page-mockup__main__link">{{$t('dashboard.design.page.link')}}</a>
        </div>
      </div>
     </div>
  </div>
</template>
<script>
const loremIpsum = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum';

export default {
  data() {
    return {
      mockupText: loremIpsum
    }
  },
  props: {
    channel: Object,
    picturesData: Object,
  }
}
</script>
