<template>
  <c-box>
    <template slot="title">{{ $t('profile.information') }}</template>
    <template slot="main">
      <div class="profile-page__info__texts">
        <div class="profile-page__info__empty" v-if="!user.about && !user.full_name  && !user.links.length">
          {{ $t('profile.personal.no_info') }}
        </div>
        <div class="profile-page__info__text" v-if="user.about && user.about !== ''" v-html="about"></div>
        <div class="profile-page__info__title" v-if="user.full_name && user.full_name !== ''">
          {{ $t('profile.personal.full_name') }}
        </div>
        <div class="profile-page__info__text" v-if="user.full_name && user.full_name !== ''">{{ user.full_name }}</div>
        <div class="profile-page__info__title" v-if="user.links.length > 0">{{ $t('profile.personal.links') }}</div>
        <div class="profile-page__info__links" v-if="user.links.length > 0">
          <div class="profile-page__info__link" :key="$index" v-for="(link, $index) in user.links">
            <span class="profile-page__info__link__name">{{ link.title }}:</span>
            <a target="_blank" :href="link.url" class="profile-page__info__link__value">{{ link.url }}</a>
          </div>
        </div>
      </div>
    </template>
  </c-box>
</template>
<style lang="scss">
.profile-page {
  &__info {
    height: 100%;
    &__empty {
      font-size: .9375em;
    }
    &__text {
      margin: 0 0 .5em;
      p {
        margin: 0 0 .5em;
      }
    }

    &__title {
      font-weight: 600;
    }

    &__link {
      &__name {
        font-weight: 600;
      }
    }
  }
}
</style>
<script>
import {parse} from 'marked';

export default {
  computed: {
    about() {
      return parse(this.user.about);
    }
  },
  props: {
    user: {
      type: Object,
      required: true
    },
  }
}
</script>
