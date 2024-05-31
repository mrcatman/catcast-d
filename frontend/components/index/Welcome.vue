<template>
  <div class="welcome">
    <c-box>
      <template slot="title">
        {{welcomeConfig.title && welcomeConfig.title.length ? welcomeConfig.title : $t('welcome.title')}}
      </template>
      <template slot="main">
        <c-row align="top">
          <c-col :grow="1.2">
            <div class="welcome__text">
            {{welcomeConfig.description || ''}}
            </div>
            {{nodeInfo}}
            <c-row align="top">
              <c-col>
                <div class="welcome__meta">
                  <div class="welcome__meta__title">
                    {{$t('welcome.meta.users')}}
                  </div>
                  <div class="welcome__meta__value">
                    1000
                  </div>
                </div>
              </c-col>
              <c-col>
                <div class="welcome__meta">
                  <div class="welcome__meta__title">
                    {{$t('welcome.meta.users')}}
                  </div>
                  <div class="welcome__meta__value">
                    1000
                  </div>
                </div>
              </c-col>
              <c-col>
                <div class="welcome__meta">
                  <div class="welcome__meta__title">
                    {{$t('welcome.meta.users')}}
                  </div>
                  <div class="welcome__meta__value">
                    1000
                  </div>
                </div>
              </c-col>
              <c-col>
                <div class="welcome__meta">
                  <div class="welcome__meta__title">
                    {{$t('welcome.meta.users')}}
                  </div>
                  <div class="welcome__meta__value">
                    1000
                  </div>
                </div>
              </c-col>
            </c-row>
          </c-col>
          <c-col>
            <div class="welcome__text">
              {{$t('welcome.about')}}
            </div>

            <c-button :to="'https://d.catcast.tv/'">{{$t('welcome.more')}}</c-button>
          </c-col>
        </c-row>
      </template>
    </c-box>
  </div>

</template>
<style lang="scss" scoped>
.welcome {
  margin: 1em;
  &__text {
    font-size: 1.125em;
    line-height: 1.4;
    margin-bottom: .5em;
  }
  &__meta {
    &__value {
      font-size: 1.75em;
      font-weight: bold;
    }
  }
}
</style>
<script>
import {mapState} from "vuex";

export default {
  mounted() {
    this.getNodeInfo();
  },
  methods: {
    async getNodeInfo() {
      this.nodeInfo = await this.$api.get('nodeinfo/2.0');
    }
  },
  data() {
    return {
      loading: true,
      nodeInfo: {}
    }
  },
  computed: {
    ...mapState('config', ['siteConfig']),
    welcomeConfig() {
      return this.siteConfig.welcome;
    }
  }
}
</script>
