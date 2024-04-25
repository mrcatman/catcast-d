<template>
<input-base :inputValue="tagsString" :type="'text'" :title="title"  :errors="errorsList" :prepend="prepend" :description="description">
  <input-tag class="input__element-container" :add-tag-on-keys="keys" v-model="val"></input-tag>
</input-base>
</template>
<style lang="scss">
.vue-input-tag-wrapper {
  border: none;
  transition: border-color 0.25s;
  position: relative;
  z-index: 1;
  font-family: inherit;
  font-size: inherit;
  outline: none !important;
  flex: 1;
  display: block;
  -webkit-appearance: initial;
  white-space: nowrap;

  .new-tag {
    background: none;
    border: none;
    outline: none;
    font: inherit;
    padding: 0;
    color: var(--input-texts-color);
    font-weight: 400;
  }

  .input-tag {
    padding: .25em .5em;
    border: 0;
    font-size: .875em;
    border-radius: .25em;
    background: var(--active-color);
    margin: .5em 0 .5em .5em;

    .remove:before {
      content: "\f00d";
      font-family: 'Font Awesome 5 Free';
      font-size: .875em;
      margin-left: .325em;

      &:hover {
        opacity: .75;
      }
    }
  }
}
</style>
<script>
import InputBase from '@/components/global/InputBase';
import inputTag from '@/components/global/input-tag/inputTag';

const keys = [32, 13, 9];

export default {
	components: {
    InputBase,
    inputTag
  },
	props: {
    title: String,
		description: String,
		prepend: String,
		errors: Array,
		value: Array,
	},
	watch: {
	  value(newVal) {
	    this.val = newVal;
    },
		val(newVal) {
		  this.$emit('input', newVal);
		},
	},
	data() {
		return {
			val: Array.isArray(this.value) ? this.value : (!!this.value ? [this.value] : []),
      tags: [],
      keys,
      formErrors: []
		}
	},
  computed: {
    errorsList() {
      return [...(this.errors ? this.errors : []), ...(this.formErrors ? this.formErrors : [])];
    },
    tagsString() {
      return this.val ? this.val.join(', ') : '';
    }
  },
	methods: {
		onKeyup(e) {
			this.$emit('keyup',e);
		},
		onChange(e) {
			this.$emit('change',e);
		}
	}
}
</script>
