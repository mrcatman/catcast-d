<template>
<inputBase class="input-base" :autoSize="autoSize"  :icon="icon" :hidePlaceholder="hidePlaceholder" :single="single" :inputValue="val" :type="type" :title="title" :placeholder="placeholder" :errors="errors" :prepend="prepend" :description="description">
  <div class="input__datetime-toggle__outer" v-click-outside="hideInput">
    <div ref="container" class="input__datetime-toggle" @click="showSelectDatetimeInput = true">
      <div class="input__element-container">
        <input disabled type="text" :value="dateTimeInputValue" class="input__element" :class="{'input__element--with-icon': icon}" />
      </div>
    </div>
    <div :style="{left: position.x+'px', top: position.y+'px'}" class="input__datetime-container" v-if="showSelectDatetimeInput" >
      <flat-pickr :config="datetimePickerConfig" v-model="dateTimePickerValue" @on-value-update="onDateChange"></flat-pickr>
    </div>
  </div>

</inputBase>
</template>
<style lang="scss">
.input {
  &__datetime-container {
    position: fixed;
    z-index: 1000000;
  }
  &__datetime-toggle{
    cursor: pointer;
    width: calc(100% - 1em);
    &__outer {
      width: 100%;
    }
  }
  &__datetime-toggle &__element{
    flex: 1;
    width: calc(100% - .5em);
  }
}
</style>
<script>
import getDate from '@/functions/getDate';
import inputBase from '@/components/elements/inputBase';
import flatPickr from '@/components/elements/flatpickr/flatpickr';
import 'flatpickr/dist/flatpickr.css';
import clickOutside from 'vue-click-outside';

  export default {
    components: {
      inputBase,
      flatPickr
    },
    directives:{
      clickOutside
    },
    props:{
      autoSize: {
        type: Boolean,
        required: false,
      },
      minDate: {
        type: [String, Number],
        required: false,
      },
      dialogTitle:{
        type:[String],
        required:false,
        default:"",
      },
      icon:{
        type:[String],
        required:false,
        default:"",
      },
      hidePlaceholder:{
        type: Boolean,
        required: false,
        default: false,
      },
      regexpReplace: {
        type:RegExp,
        required:false,
      },
      single:{
        type:Boolean,
        required:false,
      },
      description:{
        type:String,
        required:false,
      },
      prepend:{
        type:String,
        required:false,
      },
      errors:{
        type:Array,
        required:false
      },
      placeholder:{
        type:String,
        required:false,
      },
      title:{
        type:String,
        required:false,
      },
      value:{
        type:[String,Number],
        required:false,
      },
      type:{
        type:String,
        required:false,
        default:'text',
      },
      min:{
        type:Number,
        required:false,
      },
      max:{
        type:Number,
        required:false,
      },
      disabled:{
        type:Boolean,
        required:false,
      },
    },
    computed: {
      dateTimeInputValue() {
         if (new Date(this.dateTimePickerValue).getTime() > 0) {
          return this.dateTimeDisplayValue;
        }
        return '';
      }
    },
    watch:{
      showSelectDatetimeInput(val) {
        if (!val) {
          if (this.dateTimePickerValue) {
            this.dateTimeDisplayValue = getDate(this.dateTimePickerValue);
            let timestamp = new Date(this.dateTimePickerValue).getTime() / 1000;
            this.$emit('input', timestamp);
          }
        } else {
          this.$nextTick(() => {
            let container = this.$refs.container;
            let rect = container.getBoundingClientRect();
            this.position.x = rect.left; // + rect.width;
            this.position.y = rect.top + 36;
          })
        }
      },
      value(newVal) {
        this.val = newVal;
        this.dateTimePickerValue = parseInt(newVal) * 1000;
        this.dateTimeDisplayValue = getDate(this.dateTimePickerValue);
      },
      dateTimePickerValue(newVal) {
        console.log(newVal);

        //this.$emit('input',newVal);
      }
    },
    data() {
      return {
        dateTimeDisplayValue: null,
        dateTimePickerValue: null,
        showSelectDatetimeInput: false,
        val: this.value || 0,
        position: {
          x: 0,
          y: 0
        },
        datetimePickerConfig:{
          minDate: this.minDate !== null ? this.minDate : 'today',
          inline: true,
          time_24hr: true,
          enableTime: true,
        },
      }
    },
    mounted() {
      if (this.value) {
        this.dateTimePickerValue = parseInt(this.value) * 1000;
        this.dateTimeDisplayValue = getDate(this.dateTimePickerValue);
      }
    },
    methods:{
      onDateChange(e) {
        this.$nextTick(() => {
          let timestamp = new Date(e[0]).getTime() / 1000;
          this.$emit('input', timestamp);
        })
      },
      hideInput() {
          console.log('hide');
        this.showSelectDatetimeInput = false;
      },
      onKeyup(e) {
        this.$emit('keyup',e);
      },
      onChange(e) {
        this.$emit('change',e);
      }
    }
  }
</script>
