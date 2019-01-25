import Vue from 'vue';
import App from './App.vue';
import { t } from '@/filters/craft';

Vue.config.productionTip = false;

Vue.filter('t', t);

new Vue({
  render: h => h(App)
}).$mount('simple-map');
