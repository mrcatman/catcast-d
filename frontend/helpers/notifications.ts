import Vue from "vue";
declare const $nuxt: any;

export const notifyErrors = (errors: string[]): void => {
  Vue.notify({
    group: "all",
    type: "error",
    title: $nuxt.$t("common.error"),
    text: errors.join("<br />"),
    duration: 10000
  });
};

export const notifySuccess = (message: string): void => {
  Vue.notify({
    group: "all",
    type: "success",
    text: message,
    duration: 10000
  });
};

export const notifyWarn = (message: string): void => {
  Vue.notify({
    type: "warn",
    text: message
  });
};
