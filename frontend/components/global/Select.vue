<template>
<input-base v-click-outside="hideSelect" ref="select" class="select" :class="{'select--opened': opened}" :title="title" :errors="errorsList" :input-value="val">
  <div ref="current" class="select__current-option" @click="opened = !opened">{{selectedOption}}</div>
  <select-options v-show="opened" :options="optionsList" :multiple="multiple" :multipleSelection="multipleSelection" @selectOption="selectOption" />
  <a class="select__icon-container">
    <i class="material-icons">{{opened ? "arrow_drop_up" : "arrow_drop_down"}}</i>
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
<script>
import clickOutside from 'vue-click-outside';
import SelectOptions from "@/components/global/select/SelectOptions";
import InputBase from "@/components/global/InputBase";
export default {
  components: {InputBase, SelectOptions},
  directives: {
    clickOutside
  },
	computed: {
    errorsList() {
      return [...(this.errors ? this.errors : []), ...(this.formErrors ? this.formErrors : [])];
    },
    optionsList() {
      let options = JSON.parse(JSON.stringify(this.options));
      if (this.showEmptyOption) {
        options.unshift({
          name: '...',
          value: null,
        });
      }
      return options;
    },
		selectedOption() {
      let name = '...';
      if (this.val !== undefined) {
        if (this.multiple) {
          const selected = this.options.filter(option => this.val.indexOf(option.value) !== -1);
          if (selected.length > 0) {
            return selected.map(item => item.name).join(', ');
          }
        } else {
          const selected = this.options.filter(option => option.value === this.val)[0];
          if (selected !== undefined) {
            return selected.name;
          }
        }
      }
			return name;
		}
	},
	props: {
	  showEmptyOption: Boolean,
		errors: Array,
		title: String,
    multiple: Boolean,
		value: {
			//required: true
		},
		options: {
			type: Array,
			required: true
		},
	},
	mounted() {
	  if ((this.val === undefined || this.val === null || this.val === "") && this.options && this.options[0]) {
			this.val = this.options[0].value;
			this.selectOption(this.options[0]);
		}
     this.setMultipleSelection();
	},
	watch: {
		value(newVal) {
			this.val = newVal;
      this.setMultipleSelection();
		},
	},
	data() {
		return {
      val: this.value,
      multipleSelection: {},
			opened: false,
      formErrors: []
		}
	},
	methods: {
    hideSelect() {
      this.opened = false;
    },
    setMultipleSelection() {
      if (!this.multiple) {
        return;
      }
      this.multipleSelection = {};
      this.val.forEach(id => {
        this.$set(this.multipleSelection, id, true);
      })
    },
		selectOption(option) {
      console.log(option);
      if (this.multiple) {
        this.$set(this.multipleSelection, option.value, !this.multipleSelection[option.value]);
        this.val = Object.keys(this.multipleSelection).filter(id => !!this.multipleSelection[id]);
        this.$emit('input', this.val);
      } else {
        this.val = option.value;
        this.$emit('input', option.value);
        this.opened = false;
      }
		}
	}
}
</script>
