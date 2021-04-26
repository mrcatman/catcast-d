import Vue from 'vue';
import { FormErrors, FormValues, FormWarnings, Warning } from '~/types/forms'

const BaseFormComponent = Vue.extend({
  props: {
    value: Boolean
  },
  data() {
    return {
      fields: [],
      form: {} as FormValues,
      errors: {} as FormErrors,
      warnings: {} as FormWarnings,
      formIsSubmitting: false,
    }
  },
  methods: {
    onSubmit(res: any) {

    },
    submit(): Promise<any> {
      return Promise.reject(null);
    },
    sendForm() {
      this.formIsSubmitting = true;
      this.errors = {};
      this.submit().then((res: any)  => {
        this.formIsSubmitting = false;
        this.onSubmit(res);
      }).catch((e: FormErrors | null) => {
        this.formIsSubmitting = false;
        if (e !== null) {
          this.errors = e;
        }
      })
    },
    validateField(field: string, value: string | null) : Array<Warning> | null  {
      return null;
    }
  },
  mounted(): void {
    for (let field of this.fields) {
      this.$watch(`form.${field}`, (value) => {
        this.$set(this.warnings, field, this.validateField(field, value));
      })
    }
  }
})


 export { BaseFormComponent, Warning};
