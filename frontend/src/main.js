import Vue from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify'
import router from './router'
import VueCookies from 'vue-cookies';
import axios from 'axios'

Vue.use({
    install (Vue) {
    Vue.prototype.$api = axios.create({
      baseURL: process.env.VUE_APP_BASE_URL
    })
  }
})

Vue.use(VueCookies)

new Vue({
  vuetify,
  router,
  render: h => h(App)
}).$mount('#app')
