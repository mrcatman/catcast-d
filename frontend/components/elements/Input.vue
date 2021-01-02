<template>
<inputBase @click="focus" class="input-base" :disabled="disabled" :autoSize="autoSize" :loading="loading" :noErrors="noErrors" :icon="icon" :inlinePlaceholder="inlinePlaceholder" :wasFocused="wasFocused" :hidePlaceholder="hidePlaceholder" :single="single" :inputValue="val" :type="type" :title="title" :placeholder="placeholder" :warnings="warnings" :errors="errors" :append="append" :prepend="prepend"  :description="description">
	<i class="material-icons input__icon" v-if="icon">{{icon}}</i>
  <div v-if="disabled" class="input__disabled-overlay"></div>
  <div class="input__element-container">

    <div ref="inputEl" contenteditable="true" v-if="inputType === 'contenteditable'" @keyup="onKeyup" @blur="wasFocused = true" @change="onChange" class="input__element" :class="inputClasses"></div>
    <input ref="inputEl" :min="min" :max="max" :disabled="disabled" :readonly="readonly" v-else-if="inputType !== 'textarea'" :type="inputType" @keyup="onKeyup" @blur="wasFocused = true" @change="onChange" v-model="val" class="input__element" :class="inputClasses" />
    <textarea ref="inputEl" v-else @keyup="onKeyup" @change="onChange" :readonly="readonly"  v-model="val" @blur="wasFocused = true" class="input__element input__element--textarea" :class="inputClasses"></textarea>

    <div class="input__element-container__right">
      <a class="input__show-password" v-if="type === 'password'" @click="showPassword = !showPassword">{{showPassword ? $t('common.inputs.hide_password') : $t('common.inputs.show_password')}}</a>
      <i class="material-icons input__icon input__icon--warning" v-if="wasFocused && warnings && warnings.length > 0">warning</i>
    </div>


  </div>
  <div class="input__loading" v-if="loading"></div>

</inputBase>
</template>
<script>
import inputBase from '@/components/elements/inputBase';
export default {
	components:{
    inputBase
  },
	props: {
    autoSize: {
      type: Boolean,
      required: false,
    },
	  loading: {
	    type: [Boolean],
      required: false,
      default: false,
    },
	  oneLine: {
	    type: [Boolean],
      default: false,
    },
    inlinePlaceholder: {
      type: [Boolean],
      default: false,
    },
	  text: {
	    type: [Function],
      required: false,
    },
	  icon: {
	    type: [String],
      required: false,
      default: "",
    },
	  hidePlaceholder: {
	    type: Boolean,
      required: false,
      default: false,
    },
		regexpReplace: {
			type: RegExp,
			required: false,
		},
		single: {
			type: Boolean,
			required: false,
		},
		description: {
			type: String,
			required: false,
		},
    append:{
      type: String,
      required: false,
    },
		prepend: {
			type: String,
			required: false,
		},
    warnings: {
      type: Array,
      required: false
    },
		errors: {
			type: Array,
			required: false
		},
		placeholder:{
			type: String,
			required: false,
		},
		title:{
			type: [String, Number],
			required: false,
		},
		value:{
			type: [String, Number],
			required: false,
		},
		type:{
			type: String,
			required: false,
			default: 'text',
		},
		min:{
			type: [Number, String],
			required: false,
		},
		max:{
			type: [Number, String],
			required: false,
		},
		disabled:{
			type: Boolean,
			required: false,
		},
    readonly:{
      type: Boolean,
      required: false,
    },
	},
  computed: {
	  inputType() {
      if (this.type === 'password' && this.showPassword) {
        return 'text';
      }
      return this.type;
    },
	  inputClasses() {
	    return {
        'input__element--disabled': this.disabled,
        'input__element--with-icon': this.icon,
        'input__element--with-warning': this.wasFocused && this.warnings && this.warnings.length > 0
      };
    }
  },
	watch:{
	  errors(newErrors) {
	    this.noErrors = false;
    },
		value(newVal) {
      if (this.type === 'contenteditable') {
        if (!this.isChanging) {
          this.$refs.inputEl.innerHTML = newVal;
        }
      }
      this.val = newVal;
		},
		val(newVal) {
	    if (this.regexpReplace) {
				newVal = this.val = newVal.replace(this.regexpReplace,'');
			}
			this.$emit('input', newVal);
		}
	},
	data() {
		return {
      wasFocused: false,
			val: this.value,
      isChanging: false,
      noErrors: false,
      showPassword: false,
    }
	},

	created() {
		if (this.value === undefined) {
			this.val = "";
		}
    if (this.errors === undefined) {
		  this.noErrors = true;
    }
	},
	methods:{
    focus() {

      this.$refs.inputEl.focus();
    },
		onKeyup(e) {
		  this.isChanging = true;
		  if (this.type === 'contenteditable') {
		    this.val = e.target.innerHTML;
      }
			this.$emit('keyup', e);
		  if (this.oneLine) {
		    //let firstLine = this.val.match(/[^\r\n]+/g)[0];
        //if (this.type === 'contenteditable') {
        //  e.target.innerHTML = firstLine;
        //  this.val = firstLine;
        //}
      }
			if (this.type === "textarea") {
        this.$refs.inputEl.style.height = "5px";
        this.$refs.inputEl.style.height = (this.$refs.inputEl.scrollHeight)+"px";
      }
      this.$nextTick(()=> {
        this.isChanging = false;
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
			this.$emit('change',e);
		}
	}
}
</script>
