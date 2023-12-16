<template>
  <c-modal v-model="active" :header="$t('themes._title')">
    <template slot="main">
      <div class="buttons-row">
        <c-button @click="setTheme('default')" :flat="currentTheme === 'default'">{{$t('themes._list.default')}}</c-button>
        <c-button @click="setTheme('modern')" :flat="currentTheme === 'modern'">{{$t('themes._list.modern')}}</c-button>
        <c-button @click="setTheme('flat')" :flat="currentTheme === 'flat'">{{$t('themes._list.flat')}}</c-button>
      </div>
    </template>
    <template slot="buttons">
      <c-button @click="active = false">{{$t('global.ok')}}</c-button>
    </template>
  </c-modal>
</template>
<script>
  export default {
    watch: {
      active(isActive) {
        if (!isActive) {
          this.$emit('close');
        }
      }
    },
    data() {
      return {
        currentTheme: this.$store.state.currentTheme,
        active: true,
      }
    },
    methods: {
      setTheme(theme) {
        this.currentTheme = theme;
        this.$store.commit('SET_CURRENT_THEME', theme);
      }
    }
  }
</script>
