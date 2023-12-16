<template>
  <div class="user-bots-page__outer">

        <c-modal v-model="chatBotPanel.visible">
          <div slot="main">
            <c-input :title="$t('dashboard.chat.bots.name')" :errors="chatBotPanel.errors.name" v-model="chatBotPanel.data.name"/>
            <div class="row row--centered">
              <div class="col">
                <c-input :disabled="!chatBotPanel.canEditCallbackUrl" :title="$t('dashboard.chat.bots.callback_url')" :errors="chatBotPanel.errors.callback_url" v-model="chatBotPanel.data.callback_url"/>
              </div>
              <div v-if="chatBotPanel.isEditing && !chatBotPanel.canEditCallbackUrl" class="col col--button-container">
                <c-button @click="editCallbackUrl()">{{$t('global.edit')}}</c-button>
              </div>
            </div>
            <div class="user-bots-page__confirm-code-info" v-if="chatBotPanel.canEditCallbackUrl">{{$t('dashboard.chat.bots.confirm_code_info')}}<strong>{{confirmCodeData ? confirmCodeData.confirm_text : $t('global.loading')}}</strong></div>
            <c-checkbox :title="$t('dashboard.chat.bots.is_public')" v-model="chatBotPanel.data.is_public"/>
            <c-input type="textarea" v-if="chatBotPanel.data.is_public" :errors="chatBotPanel.errors.description" :title="$t('dashboard.chat.bots.description')" v-model="chatBotPanel.data.description"/>
          </div>
          <div class="modal__buttons" slot="buttons">
            <div class="buttons-row">
              <c-button :loading="chatBotPanel.loading" @click="saveBot()">{{chatBotPanel.isEditing ? $t('global.save') : $t('global.add')}}</c-button>
              <c-button flat @click="chatBotPanel.visible = false">{{ $t('global.cancel')}}</c-button>

            </div>
          </div>
        </c-modal>

        <c-modal v-model="botDeletePanel.visible">
          <div slot="main">
            <div class="modal__text">
              {{$t('global.are_you_sure')}}
            </div>
          </div>
          <div class="modal__buttons" slot="buttons">
            <div class="buttons-row">
              <c-button :loading="botDeletePanel.loading" @click="deleteBot()">{{$t('global.ok')}}</c-button>
              <c-button flat @click="botDeletePanel.visible = false">{{ $t('global.cancel')}}</c-button>
            </div>
          </div>
        </c-modal>


        <c-modal v-model="botDataPanel.visible">
          <div slot="main">
            <div><strong>{{$t('dashboard.chat.bots.request_token')}}: </strong>{{botDataPanel.data.request_token}}</div>
            <div><strong>{{$t('dashboard.chat.bots.access_token')}}: </strong>{{botDataPanel.data.access_token}}</div>
          </div>
        </c-modal>


        <div class="user-bots-page">
          <div class="user-bots-page__header">
            <span class="user-bots-page__header__inner">{{$t('dashboard.chat.bots._title')}}</span>
            <div class="buttons-row">
              <c-button icon="fa-plus" @click="showBotPanel()">{{$t('dashboard.chat.bots.add_new')}}</c-button>
              <c-button to="/help/chat-bots">{{$t('dashboard.chat.bots.go_to_help_page')}}</c-button>
            </div>
          </div>
        <!--  <div class="user-bots-page__description">{{$t('dashboard.chat.bots._description')}}</div> -->
          <div class="user-bots-page__list">
            <div class="list-container">
              <c-infinite-scroll :loading="loading" @scroll="loadMore" class="list-container__inner">
                <a class="list-item" :key="$index" v-for="(bot, $index) in bots.data">
                  <div class="list-item__left">
                    <div class="list-item__captions">
                      <div class="list-item__title">{{bot.name}}</div>
                      <div v-if="bot.connected_channels && bot.connected_channels.length > 0" class="list-item__under-title">
                        <strong>{{$t('dashboard.chat.bots.connected_channels')}}: </strong>{{bot.connected_channels.map(channel => channel.name).join(", ")}}
                      </div>
                    </div>
                  </div>
                  <div class="list-item__right">
                    <div class="buttons-row">
                      <c-button :loading="bot.loadingData" @click="showBotData(bot)">
                        {{$t('dashboard.chat.bots.show_bot_data')}}
                      </c-button>
                      <c-button color="green"  @click="editBot(bot)">
                        {{$t('global.edit')}}
                      </c-button>
                      <c-button color="red" @click="startDeletingBot(bot)">{{ $t('global.delete')}}</c-button>
                    </div>
                  </div>
                </a>
              </c-infinite-scroll>
            </div>

          </div>
          <div class="user-bots-page__footer">
            <c-pager @pageChange="onPageChange" :data="bots"/>
          </div>
        </div>
      </div>
</template>
<style lang="scss">
  .user-bots-page {
    display: flex;
    flex-direction: column;
    height: 100%;
    &__outer {
      height: 100%;
    }
    &__list {
      overflow: hidden;
      height: calc(100% - 6em);
    }


    &__confirm-code-info {
      margin: 1em 0;
    }
    &__footer {
      padding: 1em;
      background: var(--box-footer-color);
    }

    &__header {
      background: var(--box-header-color);
      color: var(--active-color);
      padding: .5em 1em;
      display: flex;
      align-items: center;
      justify-content: space-between;
      &__inner {
        font-weight: 500;
      }
    }
    &__description {
      background: var(--box-color);
      padding: .5em 1em;
    }
  }
</style>
<script>
  export default {
    components: {

    },
    methods: {
      startDeletingBot(bot) {
        this.botDeletePanel.data = bot;
        this.botDeletePanel.visible = true;
      },
      async onPageChange(page) {
        this.loading = true;
        this.currentPage = page;
        this.$axios.get(`chat/bots/getmy?page=${this.currentPage}`).then(res => {
          this.bots = res.data.data.list;
          this.loading = false;
        })
      },
      loadMore() {
        if (!this.loading) {
          if (this.currentPage < this.bots.last_page) {
            this.currentPage++;
            this.loading = true;
            this.$axios.get(`chat/bots/getmy?page=${this.currentPage}`).then(res => {
              this.bots.data = [...this.bots.data, ...res.data.data.list.data];
              this.loading = false;
            })
          }
        }
      },
      showBotData(bot) {
        this.$set(bot, 'loadingData', true);
        this.$forceUpdate();
        this.$axios.post(`chat/bots/${bot.id}/getdata`).then(res => {
          this.$set(bot, 'loadingData', false);
          if (res.data.status) {
            this.botDataPanel.data = res.data.data;
            this.botDataPanel.visible = true;
          } else {
            this.$store.commit("NEW_ALERT", res.data);
          }
        })
      },
      editCallbackUrl() {
        if (!this.confirmCodeData) {
          this.$axios.post('chat/bots/getconfirmcode', {}).then(res => {
            if (!res.data.status) {
              this.$store.commit("NEW_ALERT", res.data);
            } else {
              this.confirmCodeData = res.data.data;
              this.chatBotPanel.data.confirm_id = res.data.data.confirm_id;
              this.chatBotPanel.canEditCallbackUrl = true;
            }
          });
        } else {
          this.chatBotPanel.canEditCallbackUrl = true;
        }
      },
      editBot(bot) {
        this.chatBotPanel.canEditCallbackUrl = false;
        this.chatBotPanel.isEditing = true;
        this.chatBotPanel.data = JSON.parse(JSON.stringify(bot));
        this.chatBotPanel.visible = true;
      },
      deleteBot() {
        this.botDeletePanel.loading = true;
        this.$axios.delete(`chat/bots/${this.botDeletePanel.data.id}`).then(res => {
          this.$store.commit("NEW_ALERT", res.data);
          if (res.data.status) {
            this.botDeletePanel.loading = false;
            this.botDeletePanel.visible = false;
            this.chatBotPanel.visible = false;
            this.bots.data.splice(this.bots.data.map(botItem => botItem.id).indexOf(res.data.data.bot.id), 1);
          }
        })
      },

      saveBot() {
        this.chatBotPanel.loading = true;
        this.$axios({
          method: this.chatBotPanel.isEditing ? 'PATCH' : 'POST',
          url: this.chatBotPanel.isEditing ? 'chat/bots/' + this.chatBotPanel.data.id : 'chat/bots',
          data: this.chatBotPanel.data,
        }).then(res => {
          this.chatBotPanel.loading = false;
          this.$store.commit("NEW_ALERT", res.data);
          this.chatBotPanel.errors = res.data.errors || {};
          if (res.data.status) {
            this.chatBotPanel.visible = false;
            if (!this.chatBotPanel.isEditing) {
              this.confirmCodeData = null;
              this.bots.data.unshift(res.data.data.bot);
            } else {
              this.bots.data.forEach((bot, index) => {
                if (bot.id === res.data.data.bot.id) {
                  this.bots.data[index] = res.data.data.bot;
                }
              })
            }
          }
        })
      },
      showBotPanel() {
        this.chatBotPanel.isEditing = false;
        this.chatBotPanel.data = {};
        this.chatBotPanel.canEditCallbackUrl = true;
        if (!this.confirmCodeData) {
          this.$axios.post('chat/bots/getconfirmcode', {}).then(res => {
            if (!res.data.status) {
              this.$store.commit("NEW_ALERT", res.data);
            } else {
              this.confirmCodeData = res.data.data;
              this.chatBotPanel.data.confirm_id = res.data.data.confirm_id;
            }
          });
        }
        this.chatBotPanel.visible = true;
      },
    },
    async asyncData({ app, params }) {
      let bots = (await app.$api.get(`/chat/bots/getmy`));
      if (!bots.status) {
        return {
          error: bots
        }
      }
      return {error: null, bots: bots.data.list};
    },
    head() {
      return {
        title: this.$t('dashboard.chat.bots.user_bots_page_title')
      }
    },
    computed: {

    },
    props: {

    },
    data () {
      return {
        currentPage: 1,
        loading: false,
        botDeletePanel: {
          data: {},
          loading: false,
          visible: false,
        },
        botDataPanel: {
          data: {},
          visible: false,
        },
        confirmCodeData: null,
        chatBotPanel: {
          isDeleting: false,
          canEditCallbackUrl: true,
          isEditing: false,
          visible: false,
          loading: false,
          data: {},
          errors: {}
        },
      }
    },
    watch: {

    },
    mounted() {

    },
  }
</script>
