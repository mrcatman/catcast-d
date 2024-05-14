<template>
    <c-modal v-model="visible" :header="standard.title">
      <template slot="main">
        <div class="standard-modal__text" v-if="standard.text !== undefined && !standard.component">
          {{standard.text !== null ? standard.text : (standard.confirm ? $t('global.are_you_sure') : null)}}
        </div>
        <c-form ref="form" v-if="standard.component" :hide-submit="true" v-model="data" :initial-values="standard.formValues">
          <component ref="custom_component" :is="standard.component" v-bind="standard.props" :data="data" />
        </c-form>
      </template>
      <template slot="buttons">
        <c-button v-if="loadedComponents && standard.confirm" :disabled="standard.buttonDisabledFn ? standard.buttonDisabledFn($refs.custom_component, $refs.form) : false" :color="standard.buttonColor !== null ? standard.buttonColor : 'red'" :loading="loading" @click="save()" >{{standard.buttonText || $t('global.delete')}}</c-button>
        <c-button v-if="standard.confirm" flat @click="cancel()" >{{standard.cancelText || $t('global.cancel')}}</c-button>
      </template>

    </c-modal>

</template>
<style lang="scss">
.standard-modal {
  &__text {
    padding-bottom: 1em;
  }
}
</style>
<script>
  import { mapState } from 'vuex';
  export default {
    methods: {
      save() {
        this.loading = true;
        let data = this.standard.formValues ? this.standard.formValues : {};
        data = {
          ...data,
          ...this.data
        }
        this.standard.fn(data, this.$refs.custom_component).then(() => { // todo: ability to call function from custom component
          this.visible = false;
        }).catch((e) => {
          console.log(e);
          this.$refs.form.$emit('response', e);
       }).finally(() => {
          this.loading = false;
        })
      },
      cancel() {
        this.visible = false;
      }
    },
    watch: {
      visible(visible) {
        if (!visible) {
          this.$store.commit('modals/hideStandardModal');
        }
      },
      'standard'() {
        this.loadedComponents = false;
        this.$nextTick(() => {
          this.loadedComponents = true;
        })
      },
      'standard.visible'(visible) {
        this.visible = visible;
      }
    },
    data() {
      return {
        loadedComponents: true,
        loading: false,
        visible: false,
        data: {}
      }
    },
    computed: {
      ...mapState('modals', ['standard'])
    },
  }
</script>
