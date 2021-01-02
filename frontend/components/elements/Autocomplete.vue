<template>
<div class="autocomplete" v-click-outside="hideAutocomplete">
	<div class="autocomplete__input" ref="input">
		<m-input :errors="errors"  @click="autocompleteVisible = true" @keyup="onInputChange" :placeholder="placeholder" v-model="val" />
		<i class="autocomplete__input__loading-icon" v-show="isLoading"><m-preloader stroke-width="5" circle-width="20" /></i>
		<div :style="{'width': variantsPosition.width+'px', 'left': variantsPosition.x+'px', 'top': variantsPosition.y+'px'}" class="autocomplete__variants" v-show="autocompleteVisible">
			<div :key="$index" v-for="(variant,$index) in autocompleteVariants" class="autocomplete__variant" @click="setVariant(variant)">{{variant[autocompleteValue]}}</div>
		</div>
	</div>

</div>
</template>
<style lang="scss">
.autocomplete {
	position: relative;
	flex :1;
	&__input {
		position: relative;
		&__loading-icon {
			position: absolute;
			top: 6px;
			right: 6px;
		}
	}
	&__variants {
		position: fixed;
		z-index: 10000;
		background: rgba(0, 0, 0, .75);
    margin-top: 2em;
		width: 100%;
		max-height: 25em;
		overflow-x: hidden;
		overflow-y: auto;
    .theme-default & {
      box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.35);
    }
	}

	&__variant {
		transition: all .35s;
		cursor: pointer;
		padding: .5em 1em;
		&:hover {
			background: rgba(255, 255, 255, 0.15);
		}
	}
}
</style>
<script>
import clickOutside from 'vue-click-outside';
export default {
	directives:{
	  clickOutside
  },
	props: {
		errors: {
			type: [Array, Object],
			required: false,
			default: ()=>{
				return []
			}
		},
		returnVariant: {
			type: [Boolean],
			required: false,
			default: false,
		},
		autocompleteValue: {
			type: [String],
			required: true,
		},
		autocompleteKey: {
			type: [String],
			required: true,
		},
		url: {
			type: [String],
			required: true,
		},
		value: {
			type: [String,Object],
			required: true,
		},
		placeholder:{
			type: [String],
			required: false,
		}
	},
	data() {
		return {
			autocompleteVisible: false,
			autocompleteVariants: [],
			isLoading: false,
			val: '',
      variantsPosition: {
			  width: 0,
        x: 0,
        y: 0,
      }
		}
	},
	watch:{
	  autocompleteVisible(isVisible) {
	    if (isVisible) {
        this.setPosition();
      }
    },
		value(newVal) {
			this.val = newVal;
		},
		val(newVal) {
			if (!this.returnVariant) {
				this.$emit('input',newVal);
			}
		},
	},
	methods: {
	  setPosition() {
      let input = this.$refs.input;
      let rect = input.getBoundingClientRect();
      this.variantsPosition.width = rect.width;
      this.variantsPosition.x = rect.left;
      this.variantsPosition.y = rect.top;
    },
		setVariant(variant) {
			if (this.returnVariant) {
				this.$emit('input',variant);
			}
			this.$emit('getvariant',variant);
			this.val = variant[this.autocompleteValue];
			this.autocompleteVisible = false;
		},
		hideAutocomplete() {
			this.autocompleteVisible = false;
		},
		onInputChange() {
	    this.$emit('change');
			if (!this.isLoading) {
				setTimeout(()=>{
					if (!this.isLoading) {
						this.isLoading = true;
						this.$axios.post('/'+this.url,{autocomplete:this.val}).then(res=>{
							this.autocompleteVisible = true;
							this.isLoading = false;
							if (res.data.data && res.data.data.list) {
                this.autocompleteVariants = res.data.data.list;
              } else {
                this.autocompleteVariants = res.data.list;
              }
						})
					}
				},500)
			}
		}
	}
}
</script>
