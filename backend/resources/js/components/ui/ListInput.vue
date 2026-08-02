<template>
  <div class="list-input">
    <c-input-title v-if="title">{{title}}</c-input-title>
    <div class="list-input__description" v-if="description">{{description}}</div>
    <div class="list-input__inner">
      <c-row :key="$index" v-for="(item, $index) in model" align="stretch">
        <c-col v-for="field in fields" :key="field.id" :grow="field.flexGrow || 1">
          <c-input :title="field.name" v-model="item[field.id]" :errors="getErrors($index, field.id)"  />
        </c-col>
        <c-col with-button :without-titles="!hasFieldNames">
          <c-button icon="close" @click="model.splice($index,1)" color="red">{{$t('global.delete')}}</c-button>
        </c-col>
      </c-row>
    </div>
    <c-button color="green" @click="addItem()" icon="insert_link">{{buttonText || $t('global.add')}}</c-button>
  </div>
</template>
<style lang="scss">
.list-input {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  &__inner {
    flex: 1;
  }
  &__description {
    font-size: .875em;
    color: var(--input-descriptions-color);
    margin: .5em 0;
  }

}
</style>
<script setup lang="ts">

interface Field {
  id: string;
  name?: string;
  flexGrow?: number;
}

const props = defineProps<{
  title?: string,
  description?: string,
  buttonText?: string,
  fields: Field[],
  errors: any
}>();

const model = defineModel<Record<string, string>[]>();

const hasFieldNames = computed(() => {
  return !!props.fields.find((field) => field.name && field.name.length)
});

const getErrors = (index: number, key: string): string[] | null => {
  return props.errors ? props.errors[index]?.[key] : null;
}


function addItem() {
  const newItem = {};
  props.fields.forEach((field) => {
    newItem[field.id] = '';
  });
  model.value.push(newItem);
}
</script>
