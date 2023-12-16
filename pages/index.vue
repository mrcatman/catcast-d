<template>
  <div class="landing__container">
   <div class="landing">
      <video class="landing__video" src="/landing-bg.mp4" autoplay muted loop></video>
      <div class="landing__texts" v-if="!aboutVisible">
        <transition name="landing-first-screen" mode="out-in">
          <div :key="$index" class="landing__texts__inner" v-if="$index==currentHeaderIndex" v-for="(header,$index) in headers">
            <h1 class="landing__header">{{headers[currentHeaderIndex].h}}</h1>
            <h2 class="landing__sub">{{headers[currentHeaderIndex].s}}</h2>
            <!--
            <div class="landing__buttons">
              <c-button @click="aboutVisible = true">{{headers[currentHeaderIndex].b}}</c-button>
            </div>
            -->
          </div>
        </transition>
      </div>
      <MainPageAbout v-else/>
    </div>
   <!-- <MainPageLists /> -->
  </div>
</template>

<script>
  import MultiScreen from '@/components/layout/MultiScreen.vue'
  import MainPageLists from '@/components/main/MainPageLists.vue'
  import MainPageAbout from '@/components/main/MainPageAbout.vue'
  export default {
    data() {
      return {
        aboutVisible: false,
        pagesCount: 2,
        currentPage: 1,
        headers: this.$t('landing.headers'),
        currentHeaderIndex: 0,
        listsScrolled: false,
      }
    },
    computed: {
      active() {
        return this.currentPage !== 2 || !this.listsScrolled;
      }
    },
    created() {
     // setInterval(()=>{
      //  if (this.currentHeaderIndex<this.headers.length-1) {
     //     this.currentHeaderIndex++;
     //   } else {
     //     this.currentHeaderIndex = 0;
     //   }
     // },7500)
    },
    components: {
      MainPageAbout,
      MainPageLists,
      MultiScreen
    },
    methods: {

    }
  }
</script>

<style lang="scss">
  .landing-first-screen-enter-active, .landing-first-screen-leave-active {
    transition: opacity .35s;
  }
  .landing-first-screen-enter, .landing-first-screen-leave-to {
    opacity: 0;
  }
  .landing {
    position: relative;

    &__header {
      font-size: 1.75em;
      margin: 0;
      font-weight: bold;
    }
    &__sub {
      font-weight: 300;
      font-size: 1.5em;
    }
    &__video {
      object-fit: cover;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
    }
    &__container {
      height: 100%;
      overflow: auto;
    }

    &__texts {
      padding: 10em 0;
      position: relative;
      z-index: 1;
      width: 100%;
      height: 100%;
      background: #00000059;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      &__inner{
        text-align: center;
      }
    }
  }

  @media screen and (max-width: 768px) {
    .landing{
      &__texts{
        height: auto;
        padding: 1em;
        width: calc(100% - 2em);
        font-size: .875em;
        &__inner{
          padding: 1em;
        }
      }
    }
  }
</style>
