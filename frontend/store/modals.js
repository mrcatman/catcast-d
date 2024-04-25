const modals = {
  namespaced: true,
  state: {
    attachments: {
      visible: false,
      index: 0,
      list: []
    },
    standard: {
      visible: false,
      confirm: false,
      title: '',
      text: '',
      fn: null,
      buttonDisabledFn: null,
      component: null,
      props: {},
      formValues: {},
    },
  },
  mutations: {
    attachmentsModal(state, {attachments, index, visible = true}) {
      state.attachments.visible = visible;
      state.attachments.list = attachments || [];
      state.attachments.index = index || 0;
    },
    attachmentsModalIndex(state, {index}) {
      state.attachments.index = index || 0;
    },
    showStandardModal(state, data) {
      state.standard = {
        ...state.standard,
        ...data,
        visible: true,
        confirm: data.confirm ? data.confirm : false,
        text: data.text ? data.text : null,
        buttonText: data.buttonText ? data.buttonText : null,
        buttonColor: data.buttonText ? data.buttonColor : null,
        cancelText: data.cancelText ? data.cancelText : null,
        component: data.component ? data.component : null,
        props: data.props ? data.props : null,
        formValues: data.formValues ? data.formValues : null,
        buttonDisabledFn: data.buttonDisabledFn ? data.buttonDisabledFn : null,
      }
    },
    hideStandardModal(state) {
      state.standard = {
        ...state.standard,
        visible: false,
      }
    }
  },

};
export default modals;
