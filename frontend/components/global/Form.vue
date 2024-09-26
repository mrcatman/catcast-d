<template>
<div class="form" @keyup="handleKeyup" ref="form">
	<c-response v-if="!useAlerts && !autoSave && !hideStatus" :data="response" />
	<div class="form__inputs">
		<slot></slot>
	</div>
  <div ref="submit" class="form__submit" :class="!box && !modal ? 'form__submit--with-border' : ''" v-if="!hideSubmit">
		<c-button primary :class="buttonClass" :disabled="disabled || hasWarnings" :loading="loading" @click="submit()">{{buttonText || $t('global.save')}}</c-button>
    <slot name="right_buttons" class="form__right-buttons"></slot>
	</div>
</div>
</template>
<style lang="scss">
.form {
	&__submit {
    display: flex;
    align-items: center;
    justify-content: space-between;
    &--with-border {
      border-top: 1px solid var(--border-color);
      padding-top: 1em;
    }
	}
  &__right-buttons {
    margin-left: auto;
  }
}

</style>
<script>
import {getParentComponent} from "@/helpers/components";

export default {
  name: 'Form',
	props: {
    method: {
      type: String,
      required: false,
      default: 'post',
    },
	  buttonClass: String,
	  disabled: Boolean,
		buttonText: String,
    successMessage: String,
		url: String,
		postData: [Object, Array],
    useAlerts: Boolean,
    hideStatus: Boolean,
    autoSave: Boolean,
    hideSubmit: Boolean,
    box: Boolean,
    modal: Boolean,
    initialValues: Object,
    value: Object,
	},
  computed: {

    hasWarnings() {
      return Object.values(this.warnings).filter(warnings => warnings && warnings.length > 0).length > 0;
    }
  },
  async mounted() {
    if (this.alreadyMounted) {
      return;
    }
    if (this.box) {
      const box = getParentComponent(this, 'c-box');
      if (box) {
        const footer = Array.from(box.$el.querySelectorAll('.box__footer')).pop();
        footer.appendChild(this.$refs.submit);
      }
    }
    if (this.modal) {
      const modal = getParentComponent(this, 'c-modal');
      await this.$nextTick();
      if (modal && modal.$el) {
        const footer = Array.from(modal.$el.querySelector('.modal__buttons')).pop();
        footer.appendChild(this.$refs.submit);
      }
    }
  },
  watch: {
    postData() {
      this.submitAutoSave();
    },
    // values() {
    //   this.submitAutoSave();
    // }
  },
	data() {
		return {
			response: {
				_has_errors: false,
				message: '',
			},
			loading: false,
      firstTimeLoaded: false,
      alreadyMounted: false,
      submitTimeout: null,
      values: {},
      errors: {},
      warnings: {},
      componentsWithValidation: [],
		}
	},
	methods: {
    allValues() {
      return {
        ...this.initialValues,
        ...this.postData,
        ...this.values
      }
    },
    submitAutoSave() {
      this.$emit('input', this.allValues());
      if (!this.autoSave) {
        return;
      }
      if (!this.firstTimeLoaded) {
        this.firstTimeLoaded = true;
        return;
      }
      clearTimeout(this.submitTimeout);
      this.submitTimeout = setTimeout(() => {
        this.submit();
      }, 2500)
    },
		handleKeyup(e) {
			this.response = {
        _has_errors: false,
        message: '',
			};
			if (e.keyCode === 13) {
			  const activeElement = document.activeElement;
        if (activeElement.tagName !== 'textarea') {
          this.submit();
        }
			}
		},
		async submit() {
		  if (!this.disabled && !this.hasWarnings) {
        this.componentsWithValidation.forEach(component => {
          component.$emit('beforeSubmit')
        })
        if (this.hasWarnings) {
          return;
        }
        this.loading = true;
        if (this.url) {
          this.$api.request({
            method: this.method,
            url: this.url,
            data: this.allValues(),
            settings: {
              noAlerts: !this.useAlerts || this.autoSave,
              notifyOnSuccess: true
            }
          }).then(response => {
            this.response = response && response.message ? response : {
              message: this.successMessage || 'global.saved'
            };
            this.$emit('success', response);
            this.$emit('response', response);
            if (this.modal) {
              const modal = getParentComponent(this, 'Modal');
              modal && modal.close();
            }
          }).catch(errorResponse => {
            errorResponse._has_errors = true;
            this.response = errorResponse && errorResponse.message ? errorResponse : {
              message: 'global.unknown_error',
              _has_errors: true
            };
            this.$emit('fail', errorResponse);
            this.$emit('response', errorResponse);
            this.errors = errorResponse.errors || {};
          }).finally(() => {
            if (this.$refs.form) {
              this.$refs.form.scrollTop = 0;
            }
            this.loading = false;
          })
        }
      }
		}
	}
}
</script>
