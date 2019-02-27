import App from './App.vue';
import { t } from './filters/craft';

const VueSimpleMapPlugin = {
	install (Vue) {
		Vue.config.productionTip = false;

		Vue.filter('t', t);
		Vue.component('simple-map', App);
	}
};

if (typeof window !== 'undefined' && window.Vue)
	window.Vue.use(VueSimpleMapPlugin);
