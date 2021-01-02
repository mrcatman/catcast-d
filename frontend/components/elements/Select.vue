<template>
<div v-click-outside="hideSelect"  ref="select" class="select" :style="{'min-width': width}">
	<div class="select__title" v-if="title || placeholder"><span v-if="placeholder">{{placeholder}}</span><span v-else>{{title}}</span></div>
	<div ref="current" class="select__current-variant" @click="visible=!visible">{{getCurrentVariantName}}</div>
	<div ref="variants" class="select__variants" :style="{left: variantsPosition.left+'px', top: variantsPosition.top+'px', width: variantsPosition.width+'px'}" v-show="visible">
		<div :ref="'variant_'+$index" :key="variant.value" class="select__variant" @click="setVariant(variant)" v-for="(variant,$index) in optionsList">
			<touch-ripple class="select__variant__inner" :speed="1" :opacity="0.25" transition="ease">{{variant.name}}</touch-ripple>
		</div>
	</div>
	<a class="select__icon-container">
		<i class="material-icons">{{visible ? "arrow_drop_up" : "arrow_drop_down"}}</i>
	</a>
	<div class="select__errors" v-show="errors && errors.length>0">
		<div v-for="(error,$index) in errors" :key="$index" class="select__error">{{$t(error)}}</div>
	</div>
</div>
</template>
<style lang="scss">
.select {
    position: relative;
    display: flex;
	  flex-direction:column;
    cursor: pointer;
    margin: 0;
    &__title{
      position: absolute;
      font-size: .75em;
      top: -1.25em;
      left: 0;
      color: var(--text-sub-color);
      .theme-flat & {
        top: -1.75em;
      }
    }

    &__current-variant{
      .theme-default & {
        background: #3a3a3a;
        box-shadow: 0 2.5px 10px -3px rgba(0, 0, 0, .75);
        padding: .5em 2.5em .5em 1em;
        transition: all 0.35s;
        &:hover {
          box-shadow: 0 5px 15px -3px rgba(0, 0, 0, .75);
          background: #3f3f3f;
        }
      }
      .theme-flat & {
        padding: .5em;
        box-shadow: 0 .5em 1em -0.25em rgba(255, 255, 255, .125);
        background: rgba(255, 255, 255, .125);
        border-radius: .25em;
      }
    }
	&__errors {
		position:absolute;
		font-size:.75em;
		color: var(--red);
		font-weight: 500;
		bottom: -1.5em;
	}
	&__variants {
		z-index:10000;
		font-size: 1em;
		position: fixed;
		width:100%;
    .theme-default & {
      background: #2d2d2d;
      box-shadow: 0 5px 15px -3px #000;
    }
    .theme-flat & {
      background: rgba(6, 25, 39, 0.75);
    }
	}
	&__variant {
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		cursor: pointer;
		transition: all 0.35s;
		&:hover {
			background: rgba(255,255,255, .05);
		}
		&__inner {
			width: 100%;
      padding: .65em .5em;
		}
	}

	&__icon-container{
		  position:absolute;
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
export default {
  directives: {
    clickOutside
  },
	computed: {
    optionsList() {
      let options = JSON.parse(JSON.stringify(this.options));
      if (this.showEmptyVariant) {
        options.unshift({
          name: '...',
          value: null,
        });
      }
      return options;
    },
		getCurrentVariantName() {
			let name = "...";
			this.options.forEach(option=>{
			  if (option.value == this.val) {
					name = option.name;
				}
			});
			return name;
		}
	},
	props:{
	  showEmptyVariant: {
	    type: Boolean,
      required: false,
    },
		errors:{
			type: [Array, Object],
			required: false,
			default: ()=>{
				return []
			}
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
			required: true
		},
		options: {
			type: Array,
			required: true
		}
	},
	mounted() {
	  if ((this.val === undefined || this.val === null || this.val === "") && this.options && this.options[0]) {
			this.val = this.options[0].value;
			this.setVariant(this.options[0]);
		}
    this.setWidth();
	},
	watch:{
    options(newOptions) {
      this.setWidth();
    },
		value(newVal) {
			this.val = newVal;
		},
    visible(isVisible) {
     if (isVisible) {
       let rect = this.$refs.current.getBoundingClientRect();
       let em = parseFloat(getComputedStyle(this.$refs.current).fontSize);
       let top = rect.top + (2 * em);
       let variantsHeight = em * 2.5 * this.optionsList.length;
       if (top + variantsHeight >= window.innerHeight) {
         top = rect.top - variantsHeight;
       }
       this.$set(this.variantsPosition,'width', rect.width);
       this.$set(this.variantsPosition,'top', top);
       this.$set(this.variantsPosition,'left', rect.left);

     }
    }
	},
	data() {
		return {
      width: null,
			val: this.value,
			visible: false,
      variantsPosition: {
        width: 0,
        left: 0,
        top: 0,
      }
		}
	},
	methods:{
    hideSelect() {
      this.visible = false;
    },
	  setWidth() {
	    let maxWidth = 0;
	    let el = document.createElement('span');
      document.body.appendChild(el);
      let em = parseFloat(getComputedStyle(el).fontSize);
      el.style.display = 'inline-block';
      el.style.opacity = 0;
	    for (let ref in this.$refs) {
	      if (ref.indexOf('variant') !== -1) {
	        if (this.$refs[ref][0]) {
            el.innerHTML = this.$refs[ref][0].textContent;
            let width = el.clientWidth;
            if (width > maxWidth) {
              maxWidth = width;
            }
          }
        }
      }
      this.width = (maxWidth + em * 4)+'px';
      el.parentNode.removeChild(el);
    },
		setVariant(variant) {
			this.val = variant.value;
			this.$emit('input', variant.value);
			this.$emit('change', variant.value);
      this.$emit('valuechange',variant);
			this.visible = false;
		}
	}
}
</script>
