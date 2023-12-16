import Vue from 'vue'
const Vue2Dragula = require('./vue2-dragula.js')
import 'dragula/dist/dragula.css'
Vue.use(Vue2Dragula.Vue2Dragula, {
  logging: {
    service: false
  }
});
