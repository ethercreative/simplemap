import App from './App.vue';
import { t } from './filters/craft';

const VueMapsPlugin = {
	install (Vue) {
		Vue.filter('t', t);
		Vue.component('maps-map', App);
	}
};

if (typeof window !== 'undefined' && window.Vue)
	window.Vue.use(VueMapsPlugin);
