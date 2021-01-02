<template>
<inputBase :autoSize="autoSize" class="input-base" :single="single" :inputValue="tagsString" :type="'text'" :title="title" :placeholder="placeholder" :errors="errors" :prepend="prepend" :description="description">
  <input-tag :add-tag-on-keys="keys" v-model="tagsArray"></input-tag>
</inputBase>
</template>
<style lang="scss">
.vue-input-tag-wrapper {
  border: none;
  transition: border-color 0.25s;
  position: relative;
  z-index: 1;
  color: #fafafa;
  font-family: inherit;
  font-size: inherit;
  outline: none !important;
  flex: 1;
  display: block;
  -webkit-appearance: initial;
  white-space: nowrap;
  .theme-default & {
    padding: 3px 5px;
    background: rgba(0, 0, 0, 0.1098);
    border: none!important;
    border-bottom: 2px solid var(--input-border-color)!important;
  }
  .theme-flat & {
    padding: .5em;
    flex-wrap: wrap;
    background: rgba(255, 255, 255, .125)!important;
    box-shadow: 0 .5em 1em -0.25em rgba(255, 255, 255, .125);
    border-radius: .25em;
  }
}

.vue-input-tag-wrapper .input-tag {
  padding: .25em .5em;
  border: 0px solid #fff;
  border-radius: .25em;
  background: var(--active-color);
  margin: 0 .25em .25em 0;
}

.vue-input-tag-wrapper .new-tag {
    margin: 0;
    font: inherit;
    color: #fff;
    font-weight: 400;
}

.vue-input-tag-wrapper .input-tag .remove {
    color: #eee;
}
</style>
<script>
import inputBase from '@/components/elements/inputBase';
import inputTag from '@/components/elements/input-tag/inputTag';
export default {
	components: {
	  inputBase,
    inputTag
  },
	props:{
    autoSize: {
      type: Boolean,
      required: false,
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
			type: [String, Array],
			required: false,
		},

	},
	watch:{
	  value(newVal) {
	    this.tagsArray = newVal;
    },
		tagsArray(newVal) {
		  this.$emit('input', newVal);
		},
	},
	data() {
		return {
			tagsArray: (this.value ? (typeof this.value === 'object' ? this.value : this.value.split(',')) : []),
      tags: [],
		}
	},
  computed: {
    keys() {
      return [
        32,
        13,
        9
      ]
    },
    tagsString() {
      return this.tagsArray.join(', ');
    }
  },
  mounted() {

  },
	methods:{
		onKeyup(e) {
			this.$emit('keyup',e);
		},
		onChange(e) {
			this.$emit('change',e);
		}
	}
}
</script>
