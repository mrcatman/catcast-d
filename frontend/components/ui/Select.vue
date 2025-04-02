<template>
  <input-base ref="select" class="select" :class="{'select--opened': opened}" :title="title" :errors="errors"
              :input-value="model">
    <div ref="current" class="select__current-option" @click="opened = !opened">{{ selectedOption }}</div>
    <select-options
        @select="selectOption"
        v-show="opened"
        :options="optionsList"
        :multiple="multiple"
        :multipleSelection="multipleSelection"
    />
    <a class="select__icon-container">
      <i class="material-icons">{{ opened ? "arrow_drop_up" : "arrow_drop_down" }}</i>
    </a>
  </input-base>
</template>
<style lang="scss">
.select {
  position: relative;
  cursor: pointer;

  &__current-option {
    width: 100%;
    padding: .5em;
    padding-right: 4em;
    background: var(--input-bg-color);
    border-radius: var(--border-radius);
    border: 1px solid var(--input-border-color);
  }

  &--opened &__current-option {
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
  }

  &__icon-container {
    position: absolute;
    right: .5em;
    top: 0;
    height: 100%;
    display: flex;
    align-items: center;
  }
}
</style>
<script lang="ts" setup>
import SelectOptions from "@/components/ui/select/SelectOptions";
import InputBase from "@/components/ui/InputBase";

export interface Option {
  name: string;
  value?: string | number;
}

export interface MultipleSelection {
  [key: string]: boolean
}

const props = defineProps<{
  showEmptyOption?: boolean,
  errors?: string[],
  title?: string,
  multiple?: boolean,
  options: Option[]
}>();


const opened = ref<boolean>(false);

const optionsList = computed(() => {
  let options = [...props.options];
  if (props.showEmptyOption) {
    options.unshift({
      name: '...',
      value: null,
    });
  }
  return options;
})

const t = ref<string | number | string[] | number[]>();

const model = defineModel<string | number | string[] | number[]>();

const multipleSelection = ref<MultipleSelection>({});

const selectOption = (option) => {
  if (props.multiple) {
    multipleSelection.value = {
      ...multipleSelection.value,
      [option.value]: !multipleSelection.value[option.value]
    }
    model.value = Object.keys(multipleSelection.value).filter(id => !!multipleSelection.value[id])
  } else {
    model.value = option.value;
    opened.value = false;
  }
}

const hideSelect = () => {
  opened.value = false;
}

const selectedOption = computed(() => {
  let name = '...';
  if (model.value !== undefined) {

    if (props.multiple) {
      const selected = props.options.filter(option => !!multipleSelection.value[option.value]);
      if (selected.length > 0) {
        return selected.map(item => item.name).join(', ');
      }
    } else {
      const selected = props.options.filter(option => option.value === model.value)[0];
      if (selected) {
        return selected.name;
      }
    }
  }
  return name;
})


onMounted(() => {
  if (props.multiple) {
    const selection = {};
    model.value!.forEach(id => {
      selection[id] = true;
    })
    multipleSelection.value = selection;
  } else if (model.value === undefined || model.value === null && props.options?.length) {
    selectOption(props.options[0]);
  }
})

</script>
