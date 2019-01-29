<template>
	<div>
		<Search
			:name="config.name"
			:service="config.geoService"
			:token="config.geoToken"
			:default-value="value.address"
			@selected="onSearchSelected"
		/>

		<img
			src="https://s14-eu5.startpage.com/cgi-bin/serveimage?url=https:%2F%2Fcartoblography.files.wordpress.com%2F2013%2F07%2Fmapbox-streets-london.jpg&sp=7b474f7c3a2e924530a505ea4825463a"
			alt="Map"
			style="width: calc(100% - 2px);height: 400px;vertical-align: middle;object-fit: cover;margin-top: 14px;border-radius: 2px 2px 0 0;border: 1px solid rgba(0, 0, 20, 0.1)"
		/>

		<Parts
			:name="config.name"
			:value="value"
		/>
	</div>
</template>

<script lang="js">
	import { Component, Vue } from 'vue-property-decorator';
	import Search from './components/Search';
	import Parts from './components/Parts';
	import GeoService from './enums/GeoService';

	@Component({
		components: {
			Search,
			Parts,
		},
	})
	export default class App extends Vue {

		// Props
		// =====================================================================

		config = {
			name: '',
			mapTiles: 'wikimedia',
			mapToken: '',
			geoService: 'nominatim',
			geoToken: '',
		};

		value = {
			address: '',
			lat: 0,
			lng: 0,
			parts: {},
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

			if (
				// TODO: Also if using MapKit tiles
				config.geoService === GeoService.AppleMapKit
			) {
				window.mapkit.init({
					authorizationCallback: done => done(config.geoToken),
				});
			}
		}

		// Events
		// =====================================================================

		onSearchSelected (item) {
			this.value = item;
		}

	}
</script>

<style lang="less" module>
</style>
