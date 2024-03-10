<template>
  <div class="support-page__container">
    <div class="centered-block">
      <div class="box box--with-header support-page__tickets">
        <div class="box__header">
          <div class="box__header__title" v-if="!currentTicket.visible">
            {{$t('tickets.heading')}}
          </div>
          <div class="box__header__title" v-else>
            {{getTitle(currentTicket.data)}}
            <span class="support-page__tickets__important" v-if="currentTicket.data.is_important">
              <i class="fa fa-exclamation-triangle"></i>
            </span>
          </div>
          <div class="box__header__buttons">
            <c-button v-if="!addNewTicketPanel.visible && !currentTicket.visible" @click="addNewTicketPanel.visible = true">{{$t('tickets.create_new')}}</c-button>
            <c-button v-else-if="currentTicket.visible" @click="currentTicket.visible = false">{{$t('tickets.back_to_list')}}</c-button>
          </div>
        </div>
        <div class="box__inner" :class="{'support-page__ticket': currentTicket.visible, 'support-page__tickets__panel': addNewTicketPanel.visible, 'support-page__tickets__list': !addNewTicketPanel.visible}">
          <div v-if="addNewTicketPanel.visible">
            <c-response :data="addNewTicketPanel.response"/>
            <c-select :errors="addNewTicketPanel.errors.category" v-model="addNewTicketPanel.data.category" :options="categoriesList" :placeholder="$t('tickets.category')"/>
            <c-input v-model="addNewTicketPanel.data.title" :title="$t('tickets.title')" :errors="addNewTicketPanel.errors.title"/>
            <c-input type="textarea" v-model="addNewTicketPanel.data.text" :title="$t('tickets.text')" :errors="addNewTicketPanel.errors.text"/>
          </div>
          <div v-else-if="currentTicket.visible">
            <div class="support-page__ticket__outer">
              <div v-if="currentTicket.loading" class="page-preloader">
                <c-preloader  />
              </div>
              <c-infinite-scroll ref="messages_list" @scrollToTop="onMessagesScrollTop()" :loading="currentTicket.loadingMessages" class="support-page__ticket__messages">
                <div class="support-page__ticket__message" :class="{'support-page__ticket__message--unread': !message.is_read}" :key="$index" v-for="(message, $index) in currentTicket.messages.data">
                  <div class="support-page__ticket__message__header">
                    <div class="support-page__ticket__message__author">{{!message.is_answer ? $t('tickets.you') : $t('tickets.administration')}}</div>
                    <div class="support-page__ticket__message__time">{{formatFullDate(message.created_at, false)}}</div>
                  </div>
                  <div class="support-page__ticket__message__text">{{message.text}}</div>
                </div>
              </c-infinite-scroll>
            </div>
          </div>
          <div class="list-container" v-else>
            <div class="list-container__inner">
              <c-nothing-found v-if="!loading && tickets.total === 0" />
              <a @click="showTicket(ticket)" class="list-item list-item--without-picture" :key="ticket.id" v-for="ticket in tickets.data">
                <div class="list-item__left">
                  <div class="list-item__captions">
                    <div class="list-item__title">{{getTitle(ticket)}}   <c-tag v-if="ticket.unread_messages > 0">+{{ticket.unread_messages}}</c-tag></div>
                    <div class="list-item__under-title">{{$t('tickets.last_update')}}: <strong>{{formatPublishDate(ticket.updated_at, false)}}</strong></div>
                  </div>
                </div>
                <div class="list-item__right">
                  {{$t('tickets.statuses.'+ticket.status)}}
                </div>
              </a>
            </div>
          </div>
        </div>
        <div class="box__footer" v-if="currentTicket.visible">
          <div class="support-page__ticket__inputs">
            <c-input type="textarea" :errors="currentTicket.errors.text" v-model="currentTicket.text"/>
            <c-button @click="sendMessage()" :disabled="currentTicket.text.length === 0">{{$t('tickets.send_message')}}</c-button>
          </div>
        </div>
        <div class="box__footer" v-else-if="addNewTicketPanel.visible">
          <div class="buttons-row">
            <c-button :loading="addNewTicketPanel.loading" @click="addTicket()">{{$t('global.ok')}}</c-button>
            <c-button flat @click="addNewTicketPanel.visible = false">{{$t('global.cancel')}}</c-button>
          </div>
        </div>
        <div class="box__footer" v-else-if="tickets.last_page > 1">
          <c-pager :data="tickets" v-model="currentPage" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import { formatPublishDate, formatFullDate } from '@/helpers/dates.js';
  export default {
    computed: {
      categoriesList() {
        return this.categories.map(category => {
          return {
            name: this.$t(category.text),
            value: category.id
          }
        })
      }
    },
    methods: {
      async onMessagesScrollTop() {
        if (!this.currentTicket.loadingMessages) {
          if (this.currentTicket.currentMessagesPage < this.currentTicket.messages.last_page) {
            this.currentTicket.loadingMessages = true;
            let messages = (await this.$api.get(`tickets/${this.currentTicket.data.id}?page=${(this.currentTicket.currentMessagesPage + 1)}`)).data;
            this.currentTicket.messages.data = [...messages.messages.data.reverse(), ...this.currentTicket.messages.data];
            this.currentTicket.currentMessagesPage++;
            this.currentTicket.loadingMessages = false;
          }
        }
      },
      sendMessage() {
        if (this.currentTicket.text.length > 0) {
          this.currentTicket.isSending = true;
          this.$axios.put(`tickets/${this.currentTicket.data.id}`, {text: this.currentTicket.text}).then(res => {
            this.currentTicket.errors = res.data.errors || {};
            this.currentTicket.isSending = false;
            if (res.data.status) {
              this.currentTicket.text = '';
              this.currentTicket.messages.data =   this.currentTicket.messages.data.map(message => {
                if (message.is_answer) {
                  message.is_read = true;
                }
                return message;
              });
              this.currentTicket.messages.data.push(res.data.data.message);
              if (this.currentPage === 1) {
                this.tickets.data = this.tickets.data.filter(ticket => ticket.id !== this.currentTicket.data.id);
                this.currentTicket.data.unread_messages = 0;
                this.tickets.data.unshift(this.currentTicket.data);
              }
            } else {

              this.$store.commit('NEW_ALERT', res.data);
            }
          })
        }
      },
      async showTicket(ticket) {
        ticket.unread_messages = 0;
        this.currentTicket.loading = true;
        this.currentTicket.visible = true;
        this.currentTicket.currentMessagesPage = 1;
        this.currentTicket.data = ticket;
        let messages = (await this.$api.get(`tickets/${ticket.id}`)).data;
        this.currentTicket.messages = messages.messages;
          this.currentTicket.messages.data =   this.currentTicket.messages.data.reverse();
        this.$nextTick(() => {
          this.$refs.messages_list.$el.scrollTop =  this.$refs.messages_list.$el.scrollHeight;
          this.currentTicket.loading = false;
        });

      },
      async load() {
        this.loading = true;
        this.tickets = (await this.$api.get(`tickets?page=${this.currentPage}`)).data.tickets;
        this.loading = false;
      },
      addTicket() {
        this.addNewTicketPanel.loading = true;
        this.$axios.post('tickets', this.addNewTicketPanel.data).then(res => {
          this.addNewTicketPanel.loading = false;
          this.addNewTicketPanel.errors = res.data.errors || {};
          this.addNewTicketPanel.response = res.data;
          if (res.data.status) {
            if (this.currentPage !== 1) {
              this.currentPage = 1;
            } else {
              this.load();
            }
            setTimeout(() => {
              this.addNewTicketPanel.visible = false;
            }, 2500)
          }
        })
      },
      formatFullDate,
      formatPublishDate,
      getTitle(ticket) {
        if (ticket.title && ticket.title.length > 0) {
          return ticket.title;
        }
        if (ticket.connected_page && ticket.connected_page.type) {
          return `${this.$t(`tickets.complaints.titles.${ticket.connected_page.type}`)} ${ticket.connected_page_title}`;
        }
        return '';
      }
    },
    async asyncData({app, params}) {
      let tickets = (await app.$api.get(`tickets`)).data.tickets;
      let categories = (await app.$api.get('tickets/categories')).data.categories;
      return {
        categories,
        tickets
      }
    },
    data() {
      return {
        currentTicket: {
          visible: false,
          loading: false,
          data: null,
          messages: {},
          text: '',
          isSending: false,
          errors: {},
          loadingMessages: false,
          currentMessagesPage: 1,
        },
        addNewTicketPanel: {
          visible: false,
          response: null,
          loading: false,
          data: {
            title: '',
            text: '',
            category: 5,
          },
          errors: {

          }
        },
        currentPage: 1,
        loading: false
      }
    },
    watch: {
      currentPage(newPage) {
        this.load();
      }
    },
    middleware: 'auth',
    head () {
      return {
        title: this.$t('tickets.heading'),
      }
    },
  }
</script>
