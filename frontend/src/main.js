import Vue from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify'
import router from './router'

import axios from 'axios'

Vue.config.productionTip = false

Vue.use({
    install (Vue) {
    Vue.prototype.$api = axios.create({
      baseURL: process.env.VUE_APP_BASE_URL
    })
  }
})

new Vue({
  vuetify,
  router,
  render: h => h(App)
}).$mount('#app')
