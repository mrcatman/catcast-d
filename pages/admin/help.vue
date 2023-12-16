<template>
  <div class="admin-help-page__container">
    <div class="centered-block">
      <div class="box box--with-header admin-help-page">
        <div class="box__header">
          <div class="box__header__title" >
            {{$t('help._title')}}
          </div>
          <div class="box__header__buttons">
            <c-button v-if="!helpPagePanel.visible" @click="newHelpPage()">{{$t('help.create_new_page')}}</c-button>
          </div>
        </div>
        <div class="box__inner" >
          <div v-if="helpPagePanel.visible">
            <c-response :data="helpPagePanel.response"/>
            <c-select :errors="helpPagePanel.errors.language" v-model="helpPagePanel.data.language" :options="languagesList" :placeholder="$t('help.language')"/>
            <c-input v-model="helpPagePanel.data.title" :title="$t('help.title')" :errors="helpPagePanel.errors.title"/>
            <c-input v-model="helpPagePanel.data.section" :title="$t('help.section')" :errors="helpPagePanel.errors.section"/>
            <c-input v-model="helpPagePanel.data.url" :title="$t('help.url')" :errors="helpPagePanel.errors.url"/>
            <c-text-editor v-model="helpPagePanel.data.contents" :title="$t('help.contents')" />
          </div>
          <div class="list-container" v-else>
            <div class="list-container__inner">
              <c-nothing-found v-if="!loading && helpPages.total === 0" />
              <a class="list-item list-item--without-picture" :key="helpPage.id" v-for="helpPage in helpPages.data">
                <div class="list-item__left">
                  <div class="list-item__captions">
                    <div class="list-item__title">{{helpPage.title}}</div>
                  </div>
                </div>
                <div class="list-item__right">
                  <c-button color="green" @click="editHelpPage(helpPage)">{{$t('global.edit')}}</c-button>
                </div>
              </a>
            </div>
          </div>
        </div>
        <div class="box__footer" v-if="helpPagePanel.visible">
          <div class="buttons-row">
            <c-button :loading="helpPagePanel.loading" @click="saveHelpPage()">{{helpPagePanel.isEditing ? $t('global.save') : $t('global.add')}}</c-button>
            <c-button flat @click="helpPagePanel.visible = false">{{$t('global.cancel')}}</c-button>
          </div>
        </div>
        <div class="box__footer" v-else-if="helpPages.last_page > 1">
          <c-pager :data="helpPages" v-model="currentPage" />
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .admin-help-page {
    .box__inner {
      overflow: auto;
      max-height: 70vh;
    }
  }
</style>
<script>
  import { formatPublishDate, formatFullDate } from '@/helpers/dates.js';
  export default {
    computed: {
      languagesList() {
        let languages = ['ru', 'en'];
        return languages.map(language => {
          return {
            name: language,
            value: language
          }
        })
      }
    },
    methods: {
      newHelpPage() {
        this.helpPagePanel.isEditing = false;
        this.helpPagePanel.data = {};
        this.helpPagePanel.visible = true;
      },
      saveHelpPage() {
        this.helpPagePanel.loading = true;
        this.$axios({
          data: this.helpPagePanel.data,
          method: this.helpPagePanel.isEditing ? 'PUT' : 'POST',
          url: this.helpPagePanel.isEditing ? `admin/helppages/${this.helpPagePanel.data.id}` : 'admin/helppages'
        }).then(res => {
          this.helpPagePanel.response = res.data;
          this.helpPagePanel.errors = res.data.errors || {};
          this.helpPagePanel.loading = false;
          if (res.data.status) {
            setTimeout(() => {
              this.load();
              this.helpPagePanel.visible = false;
            })
          } else {
            this.$store.commit('NEW_ALERT', res.data);
          }
        })
      },
      editHelpPage(ticket) {
        this.helpPagePanel.isEditing = true;
        this.helpPagePanel.data = ticket;
        this.helpPagePanel.visible = true;
      },
      async load() {
        this.loading = true;
        this.helpPages = (await this.$api.get(`admin/helppages?page=${this.currentPage}`)).data.pages;
        this.loading = false;
      },
      formatFullDate,
      formatPublishDate,
    },
    async mounted() {
      this.loading = true;
      let data = (await this.$api.get(`admin/helppages`));
      if (data.status) {
        this.helpPages = data.data.pages;
      } else {
        this.$store.commit('NEW_ALERT', data);
      }
      this.loading = false;
    },
    data() {
      return {
        helpPages: {},
        helpPagePanel: {
          isEditing: false,
          visible: false,
          response: null,
          loading: false,
          data: {

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
    head () {
      return {
        title: this.$t('help._title'),
      }
    },
  }
</script>
