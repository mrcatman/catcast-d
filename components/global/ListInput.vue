<template>
  <div class="list-input">
    <c-input-title v-if="title">{{title}}</c-input-title>
    <div class="list-input__description" v-if="description">{{description}}</div>
    <div class="list-input__inner">
      <c-row :key="$index" v-for="(item, $index) in val" align="stretch">
        <c-col v-for="field in fields" :key="field.id" :grow="field.flexGrow || 1">
          <c-input :title="field.name" v-model="item[field.id]" :errors="getErrors($index, field.id)"  />
        </c-col>
        <c-col with-button :without-titles="!hasFieldNames">
          <c-button icon="close" @click="val.splice($index,1)" color="red">{{$t('global.delete')}}</c-button>
        </c-col>
      </c-row>
    </div>
    <c-button color="green" @click="addItem()" icon="insert_link">{{buttonText || $t('global.add')}}</c-button>
  </div>
</template>
<style lang="scss">
.list-input {
  --vertical-margin: .5em;
  &__description {
    font-size: .875em;
    color: var(--input-descriptions-color);
    margin: .5em 0;
  }

}
</style>
<script>
export default {
  props: {
    value: Array,
    fields: {
      type: Array,
      required: true
    },
    errors: [Object, Array],
    title: String,
    description: String,
    buttonText: String,
  },
  watch: {
    val(newItems) {
      this.$emit('input', newItems);
    }
  },
  data() {
    return {
      val: this.value || [],
      formErrors: {}
    }
  },
  methods: {
    getErrors(index, key) {
      let errors = [];
      if (this.errors && this.errors[index] && this.errors[index][key]) {
        errors = this.errors[index][key];
      }
      if (this.formErrors && this.formErrors[index] && this.formErrors[index][key]) {
        errors = this.formErrors[index][key];
      }
      return errors;
    },
    addItem() {
      const newItem = {};
      this.fields.forEach(field => {
        newItem[field.id] = '';
      })
      if (!this.val) {
        this.val = [];
      }
      this.val.push(newItem);
    },
  },
  computed: {
    hasFieldNames() {
      return this.fields.filter(field => field.name && field.name.length).length > 0;
    }
  }
}
</script>

