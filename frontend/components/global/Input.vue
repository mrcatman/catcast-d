<template>
<input-base class="input-base"
           :disabled="disabled"
           :loading="loading"
           :icon="icon"
           :wasFocused="wasFocused"
           :inputValue="val"
           :type="type"
           :title="title"
           :description="description"
           :warnings="warningsList"
           :errors="errorsList"
           :prepend="prepend"
           :append="append"
          >

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
      :attrs="$attrs"
      :class="inputClasses">
    </div>

    <input
      ref="input"
      v-else-if="type !== 'textarea'"
      :maxlength="getMaxLength"
      :placeholder="placeholder"
      :type="displayType"
      :readonly="readonly"
      :disabled="disabled"
      @keyup="onKeyup"
      @blur="wasFocused = true"
      @change="onChange"
      v-model="val"
      :attrs="$attrs"
      :class="inputClasses" />

    <textarea
      ref="textarea"
      v-else
      :maxlength="getMaxLength"
      :placeholder="placeholder"
      :readonly="readonly"
      :disabled="disabled"
      @keyup="onKeyup"
      @change="onChange"
      v-model="val"
      @blur="wasFocused = true"
      :attrs="$attrs"
      :class="inputClasses">
    </textarea>
    <div class="input__icons input__icons--right" v-if="type === 'password' || (warningsList && warningsList.length)">
      <c-icon icon="key" class="input__show-password-icon" :class="{'input__show-password-icon--active': showPassword}" v-if="type === 'password'" @click.native="showPassword = !showPassword" />
      <c-icon icon="warning" class="input__warning-icon" v-if="warningsList && warningsList.length" />
    </div>
    <slot name="buttons"></slot>
  </div>

</input-base>
</template>
<script>
import InputBase from '@/components/global/InputBase';

let debounceTimeout;

export default {
  components: {
    InputBase
  },
  props: {
    loading: Boolean,
    icon: String,
    regex: RegExp,
    title: String,
    description: String,
    append: String,
    prepend: String,
    warnings: Array,
    errors: Array,
    placeholder: String,
    value: [String, Number],
    type: {
      type: String,
      required: false,
      default: 'text',
    },
    disabled: Boolean,
    readonly: Boolean,
    maxlength: Number,
    min: Number,
    max: Number,
    debounce: Boolean
  },
  computed: {
    getMaxLength() {
      if (this.maxlength) {
        return this.maxlength;
      }
      return this.type !== 'textarea' ? 255 : 1000;
    },
    errorsList() {
      return [...(this.errors ? this.errors : []), ...(this.formErrors ? this.formErrors : [])];
    },
    warningsList() {
      return [...(this.warnings ? this.warnings : []), ...(this.formWarnings ? this.formWarnings : [])];
    },
    displayType() {
      return this.showPassword ? 'text' : this.type;
    },
    inputClasses() {
      return {
        'input__element': true,
        'input__element--textarea': this.type === 'textarea',
        'input__element--disabled': this.disabled,
        'input__element--with-icon': this.icon,
        'input__element--with-warning': this.wasFocused && this.warnings && this.warnings.length > 0
      }
    }
  },
  watch: {
    value(newVal) {
      if (this.type === 'contenteditable') {
        if (!this.changing) {
          this.$refs.contenteditable.innerHTML = newVal;
        }
      }
      this.val = newVal;
    },
    val(newVal) {
      if (this.regex) {
        newVal = this.val = newVal.replace(this.regex, '');
      }
      if (this.debounce) {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
          this.$emit('input', this.val);
        }, 400);
      } else {
        this.$emit('input', newVal);
      }
    }
  },
  data() {
    return {
      wasFocused: false,
      val: this.value || '',
      changing: false,
      showPassword: false,
      formErrors: [],
      formWarnings: [],
    }
  },
  mounted() {
  },
  methods: {
    focus() {
      if (this.$refs.input) {
        this.$refs.input.focus();
      }
    },
    onKeyup(e) {
      this.changing = true;
      if (this.type === 'contenteditable') {
        this.val = e.target.innerHTML;
      }
      this.$emit('keyup', e);
      if (this.type === 'textarea') {
        this.$refs.textarea.style.height = '5px';
        this.$refs.textarea.style.height = (this.$refs.textarea.scrollHeight) + "px";
      }
      this.$nextTick(() => {
        this.changing = false;
      });
    },
    onChange(e) {
      if (this.type === 'number') {
        let min = this.min;
        let max = this.max;
        if (this.min !== undefined && this.val < min) {
          this.val = min;
        } else {
          if (this.max !== undefined && this.val > max) {
            this.val = max;
          }
        }
      }
      this.$emit('change', e);
    }
  }
}
</script>
