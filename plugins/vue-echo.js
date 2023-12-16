import Vue from 'vue';
import VueEcho from 'vue-echo';

window.io = require('socket.io-client');

Vue.use(VueEcho, {
  broadcaster: 'socket.io',
  host: 'http://localhost:6001',
});
