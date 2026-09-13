<template>
<input-base class="input-base" v-bind="props" :input-value="model">

  <div class="input__element-container">
    <div class="input__icons input__icons--left">
      <i class="material-icons" v-if="icon">{{icon}}</i>
     </div>

    <div
      ref="contenteditable"
      contenteditable="true"
      v-if="type === 'contenteditable'"
      @keyup="onKeyup"
      @blur="wasFocused = true"
      @change="onChange"
      :attrs="attributes"
      :class="inputClasses">
    </div>

    <input
      ref="input"
      v-else-if="type !== 'textarea'"
      :maxlength="getMaxLength"
      :type="displayType"
      :attrs="attributes"
      @keyup="onKeyup"
      @blur="wasFocused = true"
      @change="onChange"
      v-model="val"

      :class="inputClasses" />

    <textarea
      ref="textarea"
      v-else
      :maxlength="getMaxLength"
      :placeholder="placeholder"
      @keyup="onKeyup"
      @change="onChange"
      v-model="val"
      @blur="wasFocused = true"
      :attrs="attributes"
      :class="inputClasses">
    </textarea>
    <div class="input__icons input__icons--right" v-if="type === 'password' || (warnings && warnings.length)">
      <c-icon icon="key" class="input__show-password-icon" :class="{'input__show-password-icon--active': showPassword}" v-if="type === 'password'" @click.native="showPassword = !showPassword" />
      <c-icon icon="warning" class="input__warning-icon" v-if="warnings && warnings.length" />
    </div>
    <slot name="buttons"></slot>
  </div>

</input-base>
</template>
<script lang="ts" setup>
import InputBase from '@/components/ui/InputBase';

let debounceTimeout;

export interface InputProps {
  loading?: boolean,
  icon?: string,
  regex?: RegExp,
  title?: string,
  description?: string,
  append?: string,
  prepend?: string,
  warnings?: string[],
  errors?: string[],
  placeholder?: string,
  type?: string;
  disabled?: boolean,
  debounce?: boolean,
}

const props = defineProps<InputProps>();
const model = defineModel<string | number>();

const val = ref<typeof model>(model.value);

const emit = defineEmits<{
  (e: 'keyup', event: KeyboardEvent): void,
  (e: 'input', event: KeyboardEvent): void,
  (e: 'change', event: KeyboardEvent): void,
}>()

const inputRef = useTemplateRef('input');
const textareaRef = useTemplateRef('textarea');

const showPassword = ref<boolean>(false);
const wasFocused = ref<boolean>(false);

const getMaxLength = computed(() => {
  if (props.maxlength) {
    return props.maxlength;
  }
  return props.type !== 'textarea' ? 255 : 1000;
})

const attrs = useAttrs();
const attributes = computed(() => {
  return {
    ...attrs.input,
    maxlength: getMaxLength.value
  };
});

const displayType = computed(() => {
  return showPassword.value ? 'text' : props.type;
})

const inputClasses = computed(() => {
  return {
    'input__element': true,
    'input__element--textarea': props.type === 'textarea',
    'input__element--disabled': props.disabled,
    'input__element--with-icon': props.icon,
    'input__element--with-warning': wasFocused.value && props.warnings && props.warnings.length
  }
})

const focus = () => {
  if (inputRef.value) {
    inputRef.value.focus();
  }
}

const onKeyup = (e: KeyboardEvent) => {
  emit('keyup', e);
  if (props.type === 'textarea') {
    textareaRef.style.height = '5px';
    textareaRef.style.height = (textareaRef.scrollHeight) + "px";
  }
}

const onChange = (e: KeyboardEvent) => {
  if (props.type === 'number') {
    if (attributes.value.min !== undefined && model.value < attributes.value.min) {
      model.value = attributes.value.min;
    } else {
      if (attributes.value.max !== undefined && model.value > attributes.value.max) {
        model.value = attributes.value.max;
      }
    }
  }
  emit('change', e);
}

watch(val, (newVal) => {
  if (props.regex) {
    newVal = this.val = newVal.replace(props.regex, '');
  }
  if (props.debounce) {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
      model.value = newVal;
    }, props.debounce || 400);
  } else {
    model.value = newVal;
  }
});

</script>
