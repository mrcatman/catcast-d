import Vue from "vue";
import Notifications from "vue-notification";

Vue.use(Notifications);
export default (context, inject) => {
  inject('notify', Vue.notify)
}
