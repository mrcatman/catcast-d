<template>
  <form class="form" @keyup="handleKeyup" ref="form" @submit.prevent="submit()">
    <c-response v-if="errorResponse" type="error" :message="errorResponse.message" />

    <div class="form__inputs">
      <slot :values="values" :errors="errors"></slot>
    </div>

    <div
        class="form__submit"
        :class="!box && !modal ? 'form__submit--with-border' : ''"
         v-if="showSubmit"
    >
      <c-button tag="button" type="submit" :disabled="disabled || hasWarnings" :loading="loading" v-bind="submitButton?.props">
        {{ submitButton?.text || $t('global.save') }}
      </c-button>
    </div>
  </form>
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
}

</style>
<script lang="ts" setup generic="T">
import { FetchError } from "ofetch";
import { type ButtonProps } from "./Button.vue";

interface FormProps {
  handler: (props: any) => any,

  disabled?: boolean,

  successMessage?: string,

  autoSave?: boolean,
  showSubmit?: boolean;

  initialValues?: Partial<T>;
  //url: String,
  //postData: [Object, Array],
  // useAlerts: Boolean,
  // hideStatus: Boolean,
  //  autoSave: Boolean,
  // hideSubmit: Boolean,
   box?: Boolean,
    modal?: Boolean,
  //  value: Object,
  submitButton?: {
    text?: string;
    props?: ButtonProps;
  }
}

const props = withDefaults(defineProps<FormProps>(), {
  showSubmit: true
});

const emit = defineEmits<{
  (e: 'error', error: Api.Error): void
  (e: 'success', response: any): void
}>()

const warnings: Array<string> = [];
const hasWarnings = computed(() => {
  return Object.values(warnings).filter(warnings => warnings && warnings.length > 0).length > 0;
})

const loading = ref(false);
const values = ref<Partial<T>>(props.initialValues ?? {});

const errorResponse = ref<Api.Error>();
const errors = ref<{
  [key: string]: string[];
}>({});

function submit() {
  errorResponse.value = null;

  if (!props.disabled && !hasWarnings.value) {
    // this.componentsWithValidation.forEach(component => {
    //   component.$emit('beforeSubmit')
    // })

    loading.value = true;
    errors.value = {};

    props.handler(
        values.value,
    ).then((successResponse: any) => {
      emit('success', successResponse);
      // this.response = response && response.message ? response : {
      //   message: this.successMessage || 'global.saved'
      // };
      // this.$emit('success', response);
      // this.$emit('response', response);
      // if (this.modal) {
      //   const modal = getParentComponent(this, 'Modal');
      //   modal && modal.close();
      // }
    }).catch((response: FetchError<Api.Error>) => {
      errorResponse.value = response.data as Api.Error || {
        message: 'global.unknown_error',
      } ;
      emit('error', errorResponse.value);
      errors.value = errorResponse.value.errors || {};

      // this.$emit('fail', errorResponse);
      // this.$emit('response', errorResponse);
      //
    }).finally(() => {
      // if (this.$refs.form) {
      //   this.$refs.form.scrollTop = 0;
      // }
      loading.value = false;
    })
  }
}

//import {getParentComponent} from "@/helpers/components";
//
// export default {
//   name: 'Form',
//
//   computed: {
//
//     hasWarnings() {
//       return Object.values(this.warnings).filter(warnings => warnings && warnings.length > 0).length > 0;
//     }
//   },
//   async mounted() {
//     if (this.alreadyMounted) {
//       return;
//     }
//     if (this.box) {
//       const box = getParentComponent(this, 'c-box');
//       if (box) {
//         const footer = Array.from(box.$el.querySelectorAll('.box__footer')).pop();
//         footer.appendChild(this.$refs.submit);
//       }
//     }
//     if (this.modal) {
//       const modal = getParentComponent(this, 'c-modal');
//       await this.$nextTick();
//       if (modal && modal.$el) {
//         const footer = Array.from(modal.$el.querySelector('.modal__buttons')).pop();
//         footer.appendChild(this.$refs.submit);
//       }
//     }
//   },
//   watch: {
//     postData() {
//       this.submitAutoSave();
//     },
//     // values() {
//     //   this.submitAutoSave();
//     // }
//   },
// 	data() {
// 		return {
// 			response: {
// 				_has_errors: false,
// 				message: '',
// 			},
// 			loading: false,
//       firstTimeLoaded: false,
//       alreadyMounted: false,
//       submitTimeout: null,
//       values: {},
//       errors: {},
//       warnings: {},
//       componentsWithValidation: [],
// 		}
// 	},
// 	methods: {
//     allValues() {
//       return {
//         ...this.initialValues,
//         ...this.postData,
//         ...this.values
//       }
//     },
//     submitAutoSave() {
//       this.$emit('input', this.allValues());
//       if (!this.autoSave) {
//         return;
//       }
//       if (!this.firstTimeLoaded) {
//         this.firstTimeLoaded = true;
//         return;
//       }
//       clearTimeout(this.submitTimeout);
//       this.submitTimeout = setTimeout(() => {
//         this.submit();
//       }, 2500)
//     },
// 		handleKeyup(e) {
// 			this.response = {
//         _has_errors: false,
//         message: '',
// 			};
// 			if (e.keyCode === 13) {
// 			  const activeElement = document.activeElement;
//         if (activeElement.tagName !== 'textarea') {
//           this.submit();
//         }
// 			}
// 		},
// 		async submit() {
//
//       }
// 		}
// 	}
// }
</script>
