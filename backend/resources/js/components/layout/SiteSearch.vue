<template>
  <div class="site-search">
    <div class="site-search__input-container" @click="showResults">
      <c-input :loading="loading" v-model="search" :inlinePlaceholder="true" icon="search" :placeholder="$t('search.placeholder')"></c-input>
      <a v-show="visible" class="site-search__close" @click="closeResults()">
        <c-icon icon="fa-times"/>
      </a>
    </div>
    <div v-show="visible" v-click-outside="hideResults" class="site-search__results">
      <div class="site-search__result-types">
        <div @click="type = 'channels'" class="site-search__result-type" :class="{'site-search__result-type--active': type === 'channels'}">
          <span class="site-search__result-type__icon">
            <i class="material-icons">tv</i>
          </span>
          <span class="site-search__result-type__text">{{$t('search.types.channels')}}</span>
        </div>
        <div @click="type = 'radio'" class="site-search__result-type" :class="{'site-search__result-type--active': type === 'radio'}">
           <span class="site-search__result-type__icon">
            <i class="material-icons">radio</i>
          </span>
          <span class="site-search__result-type__text">{{$t('search.types.radio')}}</span>
        </div>
        <div @click="type = 'videos'" class="site-search__result-type" :class="{'site-search__result-type--active': type === 'videos'}">
           <span class="site-search__result-type__icon">
            <i class="material-icons">video_library</i>
          </span>
          <span class="site-search__result-type__text">{{$t('search.types.videos')}}</span>
        </div>
        <div @click="type = 'records'" class="site-search__result-type" :class="{'site-search__result-type--active': type === 'records'}">
           <span class="site-search__result-type__icon">
            <i class="material-icons">audiotrack</i>
          </span>
          <span class="site-search__result-type__text">{{$t('search.types.records')}}</span>
        </div>
        <div @click="type = 'users'" class="site-search__result-type" :class="{'site-search__result-type--active': type === 'users'}">
           <span class="site-search__result-type__icon">
            <i class="material-icons">person</i>
          </span>
          <span class="site-search__result-type__text">{{$t('search.types.users')}}</span>
        </div>
        <div @click="type = 'playlists'" class="site-search__result-type" :class="{'site-search__result-type--active': type === 'playlists'}">
           <span class="site-search__result-type__icon">
            <i class="material-icons">folder_open</i>
          </span>
          <span class="site-search__result-type__text">{{$t('search.types.playlists')}}</span>
        </div>
      </div>
      <div class="site-search__results__list">
        <div class="site-search__results__nothing-found" v-if="!loading && search.length > 0 && searchResponse.total === 0">
          {{$t('search.nothing_found')}}
        </div>
        <div v-else class="site-search__results__inner">
          <div class="site-search__results__count" v-if="searchResponse.total > 0">
            <span class="site-search__results__count__text">
              {{$tc('search.found', searchResponse.total)}} {{$tc('search.found_number.'+this.type, searchResponse.total)}}
            </span>
            <c-button :to="searchLink" @click.native="visible = false">{{$t('search.go_to_results')}}</c-button>
          </div>
          <div @click="goLink(result)" :key="$index" v-for="(result, $index) in results" class="site-search__result" :class="'site-search__result--'+type" >
            <div v-if="type === 'records' && !result.picture" class="site-search__result__no-track-cover">
              <i class="material-icons">audiotrack</i>
            </div>
            <div class="site-search__result__picture" v-if="result.picture" :style="'background: url('+result.picture+') no-repeat center center;background-size: cover;'"></div>
            <div class="site-search__result__texts">
              <div class="site-search__result__title" v-html="getHighlights(result.title)"></div>
              <div class="site-search__result__description" v-html="getHighlights(result.description)"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .site-search {
    position: relative;
    max-width: 50em;
    margin: 0 auto;
    &__results {
      color: var(--text-color);
      position: absolute;
      top: 2.5em;
      left: 0;
      width: 100%;
      background: var(--input-bg-color);
      display: flex;
      background: var(--box-color);
      &__count {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1em;
        background: var(--box-footer-color);
        font-weight: 500;
      }
      &__nothing-found {
        padding: 1em;
        text-align: center;
        height: calc(100% - 2em);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25em;
      }
      &__list {
        width: 100%;
        max-height: 60vh;
        overflow: auto;
      }
    }
    &__close {
      position: absolute;
      top: 0;
      right: 0;
      opacity: .75;
      transition: all .4s;
      line-height: 1.5;
      cursor: pointer;
      z-index: 1000;
      height: 100%;
      display: flex;
      align-items: center;
      padding: 0 .625em;
      &:hover {
        opacity: .5;
      }
    }

    &__result {
      padding: .5em;
      display: flex;
      align-items: center;
      cursor: pointer;
      transition: all .25s;
      text-decoration: none;
      &:hover {
        background: rgba(255, 255, 255, 0.05);
      }
      &__picture {
        width: 2.5em;
        height: 2.5em;
        background-size: cover !important;
        margin: 0 .5em 0 0;
      }
      &__texts {
        width: 100%;
        max-width: calc(100% - 3.5em);
      }
      &__title {
        font-weight: 600;
      }
      &--videos &__picture {
        width: 4.5em;
      }
      &--videos &__texts {
        max-width: calc(100% - 5em);
      }
      .site-search__result__texts {
        width: 100%;
      }

      &__no-track-cover {
        background: rgba(255, 255, 255, .1);
        width: 2em;
        height: 2em;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--border-radius);
        margin: 0 .5em 0 0;
      }
    }

    &__result-types {
      max-height: 21em;
      display: flex;
      flex-direction: column;
      justify-content: space-around;
      background: var(--title-box-color);
    }

    &__result-type {
      padding: .5em 1.25em .25em;
      cursor: pointer;
      flex: 1;
      text-align: center;
      transition: all .4s;
      display: flex!important;
      align-items: center;
      &__text {
        margin: -.25em 0 0 .75em;
      }

      &:hover {
        background: rgba(255, 255, 255, .1);
      }
      &--active {
        background: var(--active-color);
        &:hover {
          background: var(--active-color);
        }
      }
    }
    @media screen and (max-width: 768px) {
       &__results {
        z-index: 10;
        flex-direction: column;
      }

      &__result-types {
        height: auto;
        flex-direction: row;
        overflow: auto;
      }


      &__result-type {
        padding: .5em;
        justify-content: center;

        &__text {
          display: none;
        }
      }
    }
  }
</style>
<script>
  import { CancelToken } from "axios"
  import clickOutside from 'vue-click-outside';
  import {MIN_SEARCH_LENGTH} from "@/constants/search";
  let searchTimeout = null;

  export default {
    directives: {
      clickOutside
    },
    computed: {
      searchLink() {
        return `/search?q=${this.search}`;
      },
      results() {
        let list = this.searchResponse.data;
        let results = [];
        list.forEach(result => {
          if (this.type === 'channels' || this.type === 'radio') {
            results.push({
              picture: result.logo,
              title: result.name,
              description: result.description,
              link: '/' + result.shortname
            })
          }
           if (this.type === 'records') {
            results.push({
              picture: result.logo,
              title: result.title,
              description: result.description,
              link: '/records/' + result.id
            })
          }
          if (this.type === 'videos') {
            results.push({
              picture: result.thumbnail,
              title: result.title,
              description: result.description,
              link: '/media/' + result.id
            })
          }
          if (this.type === 'users') {
            results.push({
              picture: result.avatar,
              title: result.username,
              description: result.status_text,
              link: '/users/' + result.id
            })
          }
          if (this.type === 'playlists') {
            results.push({
              picture: result.logo,
              title: result.name,
              description: result.description,
              link: '/playlists/' + result.id
            })
          }
        });
        return results;
      }

    },
    data() {
      return {
        search: '',
        type: 'channels',
        loading: false,
        searchResponse: {
          total: 0,
          data: [],
        },
        visible: false,
        cancelTokenSource: null,
      }
    },
    watch: {
      type() {
        if (searchTimeout) {
          clearTimeout(searchTimeout);
        }
        if (this.search.length >= MIN_SEARCH_LENGTH) {
          this.visible = true;
          this.onSearchChange();
        }
      },
      search(searchVal) {
        if (searchTimeout) {
          clearTimeout(searchTimeout);
        }
        if (searchVal.length >= MIN_SEARCH_LENGTH) {
          this.visible = true;
          searchTimeout = setTimeout(() => {
            this.onSearchChange();
          }, 300);
        }
      }
    },
    methods: {
      closeResults() {
        this.$nextTick(() => {
          this.visible = false;
        })
      },
      goLink(result) {
        this.visible = false;
        this.$router.push(result.link);
        this.$emit('hide');
      },
      showResults() {
        this.$nextTick(() => {
          this.visible = true;
        })
      },
      hideResults() {
        this.visible = false;
      },
      getHighlights(text) {
        if (!text) {
          return '';
        }
        text = text.replace(/<\/?[^>]+(>|$)/g, "");

        const textLength = text.length;
        const search = this.search.toLowerCase();

        if (search.length === 0) {
          return text;
        }
        let lowercaseText = text.toLowerCase();

        const startReplacement = '<span class="highlight">';
        const endReplacement = '</span>';
        const offsetCount = startReplacement.length + endReplacement.length;
        const maxTextSize = 250;

        let index = 0;
        let offset = 0;

        let firstMatch = lowercaseText.indexOf(search);
        if (firstMatch !== -1) {
          if (text.length > maxTextSize) {
            const start = (firstMatch - maxTextSize / 2) > 0 ? (firstMatch - maxTextSize / 2) : 0;
            const end = firstMatch + maxTextSize / 2;
            text = text.substring(start, end);
            lowercaseText = lowercaseText.substring(start, end);

          }
        } else {
          if (text.length > maxTextSize) {
            text = text.substring(0, maxTextSize) + "...";
          }

        }

        while (index !== -1) {
          index = lowercaseText.indexOf(search);
          if (index !== -1) {
            text = text.substr(0, index + offset) + startReplacement + text.substr(index + offset);
            text = text.substr(0, index + startReplacement.length + search.length + offset) + endReplacement + text.substr(index + startReplacement.length + search.length + offset);
            offset += index + offsetCount + search.length;
          }
          lowercaseText = lowercaseText.substring(index + search.length);
        }

        if ((firstMatch + maxTextSize / 2) < textLength) {
          text = text + "...";
        }
        if ((firstMatch - maxTextSize / 2) > 0) {
          text = "..." + text;
        }
        return text;
      },
      onSearchChange() {
        // todo: change
        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel();
        }
        this.cancelTokenSource = CancelToken.source();
        this.loading = true;

        let url = `${this.type}?count=5&search=${this.search}`;


        this.$api.get(url, {
          axios: {
            cancelToken: this.cancelTokenSource.token
          },
        }).then(res => {
          this.searchResponse = res;
          this.visible = true;
          this.$forceUpdate();
        }).finally(() => {
          this.loading = false;
        });
      }
    }
  }
</script>
