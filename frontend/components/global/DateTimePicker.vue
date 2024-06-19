<template>
  <div class="datetime-picker" v-click-outside="hidePicker" ref="container">
     <c-input :value="dateTimeInputValue" :readonly="true" :title="title" :placeholder="placeholder" :errors="errorsList"  @click.native="opened = true" />
     <flat-pickr v-if="opened" :config="datetimePickerConfig" v-model="dateTimePickerValue" ref="picker"></flat-pickr>
  </div>
</template>
<style lang="scss">
.datetime-picker {
  position: relative;

  .flatpickr-calendar {
    position: absolute;
    top: calc(100% + .5em);
    box-shadow: none;
    background: var(--box-color);
    border: 1px solid var(--border-color);
    z-index: 10;
  }

  .flatpickr-input {
    display: none;
  }

  .flatpickr-months .flatpickr-month {
    color: var(--text-color);
    height: unset;
    overflow: visible;
  }

  .flatpickr-day {
    color: var(--text-color);
    border: none;
  }

  .flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover, .flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay, .flatpickr-day.notAllowed, .flatpickr-day.notAllowed.prevMonthDay, .flatpickr-day.notAllowed.nextMonthDay {
    color: var(--text-color);
    opacity: .25;
  }

  .flatpickr-day:hover {
    background: var(--active-color);
  }

  span.flatpickr-weekday {
    color: var(--text-color);
    font-weight: 600;
  }

  .flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month {
    fill: var(--text-color);
    padding: 0 1em;
    height: unset;
    position: unset;
  }

  .flatpickr-months .flatpickr-prev-month:hover svg, .flatpickr-months .flatpickr-next-month:hover svg {
    fill: var(--active-color);
  }

  .flatpickr-time input {
    color: var(--text-color);
    font: inherit;
  }

  .flatpickr-time input:hover, .flatpickr-time .flatpickr-am-pm:hover, .flatpickr-time input:focus, .flatpickr-time .flatpickr-am-pm:focus {
    background: none;
  }

  .flatpickr-calendar.hasTime .flatpickr-time {
    border-top-color: var(--border-color);
  }

  .flatpickr-time .flatpickr-time-separator, .flatpickr-time .flatpickr-am-pm {
    color: var(--text-color);
  }

  .flatpickr-current-month {
    font-size: 1.25em;
    height: unset;
    padding: 0;
    position: unset;
    display: flex;
    width: 100%;
    justify-content: center;
  }

  .flatpickr-months {
    align-items: center;
    padding: 1em 0;
  }

  .flatpickr-months .flatpickr-prev-month svg, .flatpickr-months .flatpickr-next-month svg {
    width: 1em;
    height: 1em;
  }

  .flatpickr-current-month .flatpickr-monthDropdown-months option {
    background-color: var(--box-color) !important;
  }

  .flatpickr-current-month .numInputWrapper span.arrowDown:after {
    border-top-color: var(--text-color);
  }

  .flatpickr-current-month .numInputWrapper span.arrowUp:after {
    border-bottom-color: var(--text-color);
  }
}
</style>
<script>
import getDate from '@/helpers/getDate';
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import clickOutside from 'vue-click-outside';
import InputBase from "@/components/global/InputBase.vue";

  export default {
    components: {
      InputBase,
      flatPickr
    },
    directives: {
      clickOutside
    },
    props: {
      value: Date,
      minDate: [Date, String],
      maxDate: [Date, String],
      enableTime: {
        type: Boolean,
        required: false,
        default: true
      },
      errors: Array,
      placeholder: String,
      title: String,
    },
    computed: {
      errorsList() {
        return [...(this.errors ? this.errors : []), ...(this.formErrors ? this.formErrors : [])];
      },
      dateTimeInputValue() {
         if (new Date(this.val).getTime() > 0) {
          return this.dateTimeDisplayValue;
        }
        return '';
      },
      datetimePickerConfig() {
        return {
          minDate: this.minDate,
          maxDate: this.maxDate,
          inline: true,
          time_24hr: true,
          enableTime: this.enableTime,
        }
      }
    },
    watch: {
      enableTime() {
        this.dateTimeDisplayValue = getDate(this.val, this.enableTime);
      },
      value(newVal) {
        this.val = newVal;
      },
      val(newVal) {
        this.dateTimeDisplayValue = getDate(newVal, this.enableTime);
        this.$emit('input', newVal);
      },
      dateTimePickerValue(newVal) {
        this.val = new Date(newVal);
      },
      async opened(opened) {
        if (opened) {
          await this.$nextTick();
          this.setPickerPosition();
        }
      }
    },
    data() {
      return {
        val: this.value,
        opened: false,
        dateTimeDisplayValue: null,
        dateTimePickerValue: null,
        formErrors: []
      }
    },
    mounted() {
      if (this.value) {
        this.dateTimeDisplayValue = getDate(this.val, this.enableTime);
      }
    },
    methods: {
      setPickerPosition() {
        const rect = this.$refs.picker?.$el?.getBoundingClientRect();
        console.log(rect);
      },
      hidePicker() {
        this.opened = false;
      }
    }
  }
</script>
