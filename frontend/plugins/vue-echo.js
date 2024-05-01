import Vue from 'vue';
import VueEcho from 'vue-echo';
import {API_URL, WEBSOCKETS_HOST} from "@/constants/urls";

window.io = require('socket.io-client');

Vue.use(VueEcho, {
  broadcaster: 'socket.io',
  host: WEBSOCKETS_HOST
});
