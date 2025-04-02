import { get as _get, set as _set } from "lodash";
import { getParentComponent } from "~/helpers/components";
//
export default defineNuxtPlugin((nuxtApp) => {
	// nuxtApp.vueApp.directive('form-input', {
	// 	beforeMount: function (el, binding, node) {
	// 		const inputName = binding.value;
	// 		const component = node.component!;
	// 		const form = getParentComponent(component, 'Form');
	//
	// 		component.$data.val = _get(form.initialValues, inputName);
	// 		component.$on('input', value => {
	// 			_set(form.values, inputName, value);
	// 			if (value && value.id) {
	// 				_set(form.values, `${inputName}_id`, value.id);
	// 			}
	// 			form.submitAutoSave();
	// 			form.$emit(`valueChanged:${inputName}`, value);
	// 		});
	// 		form.$on('response', response => {
	// 			component.formErrors = response.errors ? _get(response.errors, inputName) : [];
	// 		})
	// 	}
	// })

	nuxtApp.vueApp.directive('form-validate', {
		beforeMount: function (el, binding, node) {
			// const component = node.component!;
			// const inputName = node.directives.filter(item => item.name === 'form-input')[0].value;
			// const form = getParentComponent(component, 'Form');
			// form.componentsWithValidation.push(component);
			//
			// const onInput = (inputValue, force) => {
			// 	if (inputValue === undefined && !force) {
			// 		return;
			// 	}
			// 	let validationError = null;
			// 	if (typeof binding.value === 'function') {
			// 		validationError = binding.value(inputValue);
			// 	} else {
			// 		switch (binding.value) {
			// 			case 'required':
			// 				validationError = inputValue === undefined || !inputValue.length ? 'errors.field_required' : null;
			// 				break;
			// 		}
			// 	}
			// 	const warnings = validationError ? [validationError] : [];
			// 	component.formWarnings = warnings;
			// 	form.$set(form.warnings, inputName, warnings);
			// };
			//
			// component.$on('input', onInput);
			// component.$on('beforeSubmit', () => onInput(_get(form.values, inputName), true));
		}
	})

	nuxtApp.vueApp.directive('form-show', {
		beforeMount: function (el, binding, node) {
			// todo: rewrite to slots

			// const inputName = binding.value.startsWith('!') ? binding.value.substring(1) : binding.value;
			// const invert = binding.value.startsWith('!');
			//
			// const component = node.component!;
			// const form = getParentComponent(component, 'Form');
			// const showHideComponent = (visibility) => {
			// 	if (component)  {
			// 		component.$el.style.display = visibility ? '' : 'none';
			// 	}
			// }
			//
			// const initialValue = _get(form.initialValues, inputName);
			// showHideComponent(!invert ? initialValue : !initialValue);
			// form.$on(`valueChanged:${inputName}`, value => {
			// 	showHideComponent(!invert ? value : !value);
			// });
		}
	})
})

