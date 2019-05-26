
import  router from './router/index';
import store from './store/index'
import App from './App.vue'
import '@/icons/index'
import '@/permission'

const app = new Vue({
    el: '#app',
    router,
    store,
    render: h => h(App)
});
