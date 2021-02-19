<template>
<div class="radio-buttons" :class="{'radio-buttons--block': block}">
	<div v-show="title && title !== ''" class="radio-buttons__title">{{title}}</div>
	<div class="radio-buttons__items" :class="{'radio-buttons__items--inline': inline}">
		<div @click="setVal(item)" :key="$index" v-for="(item, $index) in values" class="radio-buttons__item" :class="{'radio-buttons__item--active': item.id === val}">
      <span class="radio-buttons__item__circle"></span>
			<span class="radio-buttons__item__text">{{item.title}}</span>
		</div>
	</div>
</div>
</template>
<style lang="scss">
.radio-buttons {
  margin: 1.25em 0;
  &__items {
    display: flex;
    flex-direction: column;

    &--inline {
      flex-direction: row;
    }
  }

  &__title {
    font-size: 1em;
    font-weight: 500;
    margin: 0 0 .5em;
  }

  &__items--inline &__item {
    margin: 0 1em 0 0;
  }

  &__item {
    display: flex;
    padding: 0 0 1em;
    cursor: pointer;
    font-weight: 500;
    font-size: .875em;
    transition: all .35s;

    &__circle {
      width: 1em;
      height: 1em;
      border: .1em solid var(--input-bg-color);
      display: block;
      border-radius: 50%;
      position: relative;

      &:before {
        content: "";
        display: block;
        width: .5em;
        height: .5em;
        background: var(--input-bg-color);
        border-radius: 50%;
        margin: .25em;
      }
    }

    &__text {
      color: var(--inactive-color);
      margin: 0 0 0 .5em;
    }

    &--active &__text {
      color: var(--text-color);
    }

    &--active &__circle {
      border-color: var(--active-color);
    }

    &--active &__circle:before {
      background: var(--active-color);
    }
  }


  &--block &__item {
    background: rgba(0, 0, 0, 0.25);
    margin: 0;
    padding: .5em 1em;
    font-size: 1em;
    transition: all .35s;

    &:hover {
      background: rgba(255, 255, 255, 0.1);

    }

    &__circle {
      display: none;
    }

    &--active {
      background: var(--active-color);
    }

    &__text {
      margin: 0;
      transition: all .35s;
    }
  }

  &--block &__item:hover &__item__text {
    color: #fff;
  }
  &--block &__items {
    border-radius: .25em;
    overflow: hidden;
    display: inline-flex;
    font-size: 1.125em;
  }
  &--block &__items {
    border-radius: .25em;
    overflow: hidden;
    display: inline-flex;
    font-size: 1.125em;
  }
}

</style>
<script>
export default {
	props: {
	  block: {
	    type: [Boolean],
      required: false
    },
    defaultValue: {
       required: false
    },
	  inline: {
	    type: [Boolean],
      required: false,
    },
		title: {
			type: [String],
			required: false,
		},
		returnTitle: {
			type: [Boolean],
			required: false,
    },
		values: {
			type: [Array],
			required: true,
		},
		value: {
			type: [Boolean, Number, String, Array],
			required: false,
		}
	},
  mounted() {
	  if (this.value === undefined && this.defaultValue !== undefined) {
	      this.val = this.defaultValue;
    }
  },
	watch:{
	  value(newVal) {
	    this.val = newVal;
    },
		selectedItem(newItem) {
			if (!this.returnTitle) {
				this.val = newItem.id;
				this.$emit('input',newItem.id);
			} else {
				this.$emit('input',newItem.title);
			}
		}
	},
	data() {
		return {
			val: this.value,
			selectedItem: null,
		}
	},
	methods:{
		setVal(item) {
		  this.$emit('select', item);
			this.selectedItem = item;
		}
	}
}
</script>
