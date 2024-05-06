<template>
  <div class="autocomplete" ref="main" v-click-outside="hideAutocomplete">
    <c-input v-model="search" :title="title" :placeholder="placeholder" @click="opened = true" @keyup="onInputChange"/>
    <c-preloader class="autocomplete__loading" v-show="loading"/>
    <select-options v-show="opened" :options="autocompleteOptions" @selectOption="selectOption"/>
  </div>
</template>
<style lang="scss">
.autocomplete {
  position: relative;
  flex: 1;

  &__loading {
    position: absolute;
    bottom: 1em;
    right: .5em;
  }

  &__variant {
    transition: all .4s;
    cursor: pointer;
    padding: .5em 1em;

    &:hover {
      background: rgba(255, 255, 255, 0.1);
    }
  }
}
</style>
<script>
import clickOutside from 'vue-click-outside';
import SelectOptions from "@/components/global/select/SelectOptions";

let loadOptionsTimeout;

export default {
  components: {SelectOptions},
  directives: {
    clickOutside
  },
  props: {
    autocompleteValue: {
      type: String,
      required: true,
    },
    autocompleteKey: {
      type: String,
      required: true,
    },
    url: {
      type: String,
      required: true,
    },
    value: String,
    title: String,
    placeholder: String,
  },
  data() {
    return {
      opened: false,
      options: [],
      loading: false,
      search: '',
      val: '',
    }
  },
  watch: {
    value(newVal) {
      this.val = newVal;
    },
    val(newVal) {
      this.$emit('input', newVal);
      if (newVal) {
        this.search = newVal[this.autocompleteValue];
      }
    },
  },
  computed: {
    autocompleteOptions() {
      return this.options.map(option => {
        return {
          name: option[this.autocompleteValue],
          value: option[this.autocompleteKey],
        }
      })
    }
  },

  methods: {
    selectOption(option) {
      this.search = option.name;
      this.val = {
        [this.autocompleteKey]: option.value,
        [this.autocompleteValue]: option.name,
      };
      this.opened = false;
    },
    hideAutocomplete() {
      this.opened = false;
    },
    onInputChange() {
      this.$emit('change');
      if (!this.loading) {
        clearTimeout(loadOptionsTimeout);
        loadOptionsTimeout = setTimeout(() => { //todo: refactor
          if (!this.loading && this.search.length > 0) {
            this.loading = true;
            this.$api.get(`${this.url}?search=${this.search}`).then(options => {
              this.opened = true;
              this.options = options.data || options;
              const hasExactOption = this.options.filter(option => option[this.autocompleteKey] === this.search)[0];
              if (!hasExactOption) {
                this.val = {
                  id: null,
                  [this.autocompleteValue]: this.search
                };
              }
            }).finally(() => {
              this.loading = false;
            })
          }
        }, 300);
      }
    }
  }
}
</script>
