import Vue from 'vue/dist/vue.min.js'
import { get as _get, set as _set} from 'lodash';
import {getParentComponent} from "@/helpers/components";
import isMobile from "@/helpers/isMobile";



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
