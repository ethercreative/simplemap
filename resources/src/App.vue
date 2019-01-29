<template>
	<div>
		<Search
			v-if="!config.hideSearch"
			:name="config.name"
			:service="config.geoService"
			:token="config.geoToken"
			:default-value="value.address"
			@selected="onSearchSelected"
		/>

		<Map
			v-if="!config.hideMap"
			:tiles="config.mapTiles"
			:token="config.mapToken"
			:latLng="{ lat: value.lat, lng: value.lng }"
			:zoom="config.zoom"
		/>

		<Address
			v-if="!config.hideAddress"
			:name="config.name"
			:value="value"
		/>
	</div>
</template>

<script lang="js">
	import { Component, Vue } from 'vue-property-decorator';
	import Search from './components/Search';
	import Address from './components/Address';
	import Map from './components/Map';
	import GeoService from './enums/GeoService';

	@Component({
		components: {
			Search,
			Address,
			Map,
		},
	})
	export default class App extends Vue {

		// Props
		// =====================================================================

		config = {
			name: '',
			zoom: 15,
			hideSearch: false,
			hideMap: false,
			hideAddress: false,
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
