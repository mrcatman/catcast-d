<template>
  <div class="lang-editor">
    <div class="lang-editor__inner" :key="key" v-for="(value, key) in val">
      <c-input :title="key" v-if="!valueIsObject(value) && !valueIsArray(value)" v-model="val[key]"/>
      <div class="lang-editor__array" v-else-if="valueIsArray(value)">
        <div v-for="(item, $index) in value" :key="$index" >
          <c-input :title="key" v-if="!valueIsObject(item)" v-model="val[key][$index]"/>
          <LangEditor v-else-if="val[key][$index]" v-model="val[key][$index]"/>
        </div>
      </div>
      <div class="lang-editor__sub" v-else-if="val[key]">
        <div class="lang-editor__sub__title">{{key}}</div>
        <LangEditor v-model="val[key]"/>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .lang-editor {
    &__sub {
      padding: 0 0 0 1em;
    }
  }
</style>
<script>
  export default {
    name: 'LangEditor',
    methods: {
      valueIsArray(value) {
        let isArray = Array.isArray(value);
        if (isArray) {
         // console.log(value);
        }
        return isArray;
      },
      valueIsObject(value) {
        return typeof value === 'object';
      }
    },
    data() {
      return {
        val: this.value
      }
    },
    watch: {
      val: {
        handler(newVal) {
          this.$emit('input', newVal);
        },
        deep: true
      }
    },
    props: {
      value: {
        type: [Object, String],
        required: true
      }
    }
  }
</script>
