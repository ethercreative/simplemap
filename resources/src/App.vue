<template>
	<div>
		<Search
			:service="config.geoService"
			:token="config.geoToken"
			:default-value="value.address"
			@selected="onSearchSelected"
		/>
	</div>
</template>

<script lang="js">
	import { Component, Vue } from 'vue-property-decorator';
	import Search from './components/Search.vue';
	import GeoService from './enums/GeoService';

	@Component({
		components: {
			Search,
		},
	})
	export default class App extends Vue {

		// Props
		// =====================================================================

		config = {
			geoService: 'nominatim',
			geoToken: '',
		};

		value = {
			address: '',
		};

		// Vue
		// =====================================================================

		created () {
			// Passing this as a prop isn't working so we're having to do it
			// manually :(
			const { config, value } = JSON.parse(
				this.$parent.$el.firstElementChild.textContent
			);

			this.config = config;
			this.value = value;

			if (config.geoService === GeoService.AppleMapKit) {
				window.mapkit.init({
					authorizationCallback: done => done(config.geoToken),
				});
			}
		}

		// Events
		// =====================================================================

		onSearchSelected (item) {
			console.log(item);
		}

	}
</script>

<style lang="less" module>
</style>
