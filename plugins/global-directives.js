import Vue from 'vue'
import { get as _get, set as _set} from 'lodash';
import {getParentComponent} from "@/helpers/components";
import isMobile from "@/helpers/isMobile";

Vue.directive('form-input', {
  bind: function (el, binding, node) {
    const inputName = binding.value;
    const component = node.componentInstance;
    const form = getParentComponent(component, 'Form');
    component.$data.val = _get(form.initialValues, inputName);
    component.$on('input', value => {
      _set(form.values, inputName, value);
      if (value && value.id) {
        _set(form.values, `${inputName}_id`, value.id);
      }
      form.submitAutoSave();
      form.$emit(`valueChanged:${inputName}`, value);
    });
    form.$on('response', response => {
      component.formErrors = response.errors ? _get(response.errors, inputName) : [];
    })
  }
})

Vue.directive('form-validate', {
  bind: function (el, binding, node) {
    const component = node.componentInstance;
    const inputName = node.data.directives.filter(item => item.name === 'form-input')[0].value;
    const form = getParentComponent(component, 'Form');
    form.componentsWithValidation.push(component);

    const onInput = (inputValue, force) => {
      if (inputValue === undefined && !force) {
        return;
      }
      let validationError = null;
      if (typeof binding.value === 'function') {
        validationError = binding.value(inputValue);
      } else {
        switch (binding.value) {
          case 'required':
            validationError = inputValue === undefined || !inputValue.length ? 'errors.field_required' : null;
            break;
        }
      }
      const warnings = validationError ? [validationError] : [];
      component.formWarnings = warnings;
      form.$set(form.warnings, inputName, warnings);
    };

    component.$on('input', onInput);
    component.$on('beforeSubmit', () => onInput(_get(form.values, inputName), true));
  }
})

Vue.directive('form-show', {
  bind: function (el, binding, node) {

    const inputName = binding.value.startsWith('!') ? binding.value.substring(1) : binding.value;
    const invert = binding.value.startsWith('!');

    const component = node.componentInstance;
    const form = getParentComponent(component, 'Form');
    const showHideComponent = (visibility) => {
      if (component.$el)  {
        component.$el.style.display = visibility ? '' : 'none';
      }
    }

    const initialValue = _get(form.initialValues, inputName);
    showHideComponent(!invert ? initialValue : !initialValue);
    form.$on(`valueChanged:${inputName}`, value => {
      showHideComponent(!invert ? value : !value);
    });
  }
})

const heightsByGroup = {};

Vue.directive('limit-height', {
  bind: function (el, binding, node) {
    if (isMobile()) {
      return;
    }

    const component = node.componentInstance;
    const groupName = binding.value;

    const initialDisplayStyle =  component.$el.style.display;
    component.$el.style.display = 'none';
    Vue.nextTick(() => {
      const box = getParentComponent(component, 'c-box');
      if (box) {
        box.$el.style.flex = '1';
        Vue.nextTick(() => {
          const boxHeight = box.$el.offsetHeight;
          const contentHeight = box.$refs.header.offsetHeight + box.$refs.main.offsetHeight;
          const componentHeight = boxHeight - contentHeight; //todo: em
          if (heightsByGroup[groupName]) {
            component.$el.style.overflow = 'auto';
            component.$el.style.height = `${(heightsByGroup[groupName])}px`;
            component.$el.style.maxHeight = `${(heightsByGroup[groupName])}px`;
            component.$el.style.display = initialDisplayStyle;
            return;
          }
          heightsByGroup[groupName] = componentHeight;
          component.$el.style.overflow = 'auto';
          component.$el.style.height = `${(componentHeight)}px`;
          component.$el.style.maxHeight = `${(componentHeight)}px`;
          component.$el.style.display = initialDisplayStyle;
        });
      }
    })

  }
})
