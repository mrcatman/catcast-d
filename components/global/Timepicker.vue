<template>
<input-base v-click-outside="hideDropdowns" class="input-base"  :inputValue="val" type="text" :title="title" :placeholder="placeholder" :errors="errors" :prepend="prepend" :description="description">
  <div @click="showDropdowns = !showDropdowns" class="input__element-container">
    <input v-mask="'##:##'" @keyup="onKeyup" @change="onChange" v-model="val" class="input__element" />
  </div>
  <div class="input__dropdowns" v-show="showDropdowns">
    <div class="input__dropdown">
      <a class="input__dropdown__variant" :class="{'input__dropdown__variant--active': hours === variant}" @click="setHours(variant)" :key="$index" v-for="(variant, $index) in variants.hours">
        {{variant >= 10 ? variant : "0"+variant}}
      </a>
    </div>
    <div class="input__dropdown">
      <a class="input__dropdown__variant" :class="{'input__dropdown__variant--active': minutes === variant}" @click="setMinutes(variant)" :key="$index" v-for="(variant, $index) in variants.minutes">
        {{variant >= 10 ? variant : "0"+variant}}
      </a>
    </div>
  </div>
</input-base>
</template>
<script>
import {mask} from 'vue-the-mask'
import InputBase from '@/components/global/InputBase';
import clickOutside from 'vue-click-outside';

export default {
  directives: {
    clickOutside,
    mask
  },
	components: {InputBase},
	props: {
		regex: {
			type: RegExp,
			required: false,
		},
		description: {
			type: String,
			required: false,
		},
		prepend: {
			type: String,
			required: false,
		},
		errors: {
			type: Array,
			required: false
		},
		placeholder: {
			type: String,
			required: false,
		},
		title: {
			type: String,
			required: false,
		},
		value: {

		},
	},
  mounted() {
    if (!this.val) {
      this.val = '12:00';
    }
    this.setVal();
  },
	watch: {
		errors(newErrors) {

		},
		value(newVal) {
			this.val = newVal;
			this.setVal();
		},
		val(newVal) {
			this.$emit('input',newVal);
		}
	},
	data() {
		return {
      showDropdowns: false,
		  hours: 0,
      minutes: 0,
			val: this.value || "12:00",
      variants: {
		    hours: [],
        minutes: [],
      }
		}
	},
	created() {

	},
	methods: {
    setVal() {
      let val = this.val.split(":");
      if (val.length === 2) {
        this.hours = parseInt(val[0]);
        this.minutes = parseInt(val[1]);
      }
      for (let i = 0; i < 24; i++) {
        this.variants.hours.push(i)
      }
      for (let i = 0; i < 60; i++) {
        this.variants.minutes.push(i)
      }
   },
    hideDropdowns() {
      this.showDropdowns = false;
    },
    setMinutes(minutes) {
      this.minutes = minutes;
      let val = this.val.split(":");
      if (val.length !== 2) {
        val[1] = "00";
      }
      val[1] = minutes > 9 ? minutes : "0"+minutes;
      this.val = val[0]+":"+val[1];
    },
    setHours(hours) {
      this.hours = hours;
      let val = this.val.split(":");
      if (val.length !== 2) {
        val[1] = "00";
      }
      val[0] = hours > 9 ? hours : "0"+hours;
      this.val = val[0]+":"+val[1];
    },
		onKeyup(e) {
			this.$emit('keyup', e);
			this.$nextTick(() => {
        let val = this.val.split(":");
        if (val.length === 2) {
          if (val[0] > 23) {
            val[0] = 23;
          }
          if (val[1] > 59) {
            val[1] = 59;
          }
          let newVal = val[0]+":"+val[1];
          console.log(newVal);
          this.$nextTick(() => {
            this.val = newVal;
          })
        }
      })

		},
		onChange(e) {
			this.$emit('change', e);
		}
	}
}
</script>
