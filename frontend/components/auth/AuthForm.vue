<template>
  <div class="centered-block auth-form">
    <c-box>
      <template slot="main">
        <div class="auth-form__inner">
          <div class="auth-form__logo">
            <img :src="siteLogo" class="auth-form__logo__picture" />
          </div>
          <slot name="main"></slot>
          <div class="auth-form__links">
            <slot name="links"></slot>
          </div>
        </div>
      </template>
    </c-box>
  </div>
</template>
<style lang="scss">
.auth-form {
  &__inner {
    width: 100%;
    min-width: 30em;
    max-width: 35em;
    max-height: calc(100vh - 3.5em);
    overflow: auto;
    padding: 1.25em;
    box-sizing: border-box;

  }
  &__links {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    a {
      cursor: pointer;
      border-bottom: 1px solid;
      text-decoration: none;
      margin-top: 1em;
    }
  }
  &__logo {
    margin-bottom: 1em;
    text-align: center;
    &__picture {
      margin: 0 auto;
      fill: var(--text-color);
      height: 5em;
    }
  }
}
</style>
<script>
import {mapGetters} from "vuex";
export default {
  computed: {
    ...mapGetters('config', ['siteLogo']),
  },

}
</script>
