<template>
	<div>
		<Search
			v-if="!config.hideSearch"
			:service="config.geoService"
			:default-value="value.address"
			:geo="geo"
			@selected="onSearchSelected"
		/>

		<Map
			v-if="!config.hideMap"
			:tiles="config.mapTiles"
			:token="config.mapToken"
			:latLng="{ lat: value.lat, lng: value.lng }"
			:zoom="value.zoom"
			@change="onMapChange"
			@zoom="onZoom"
		/>

		<Address
			v-if="!config.hideAddress"
			:value="value"
			@changed="onPartChange"
		/>

		<input
			type="hidden"
			:name="this.config.name"
			:value="JSON.stringify(value)"
		/>
	</div>
</template>

<script lang="js">
	import { Component, Vue } from 'vue-property-decorator';
	import Search from './components/Search';
	import Address from './components/Address';
	import Map from './components/Map';
	import Geo from './common/Geo';
	import GeoService from './enums/GeoService';
	import Parts from './models/Parts';

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
			zoom: 15,
			lat: 0,
			lng: 0,
			parts: new Parts(),
		};

		geo = null;

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
			this.value.parts = Parts.from(value.parts);

			this.geo = new Geo(config);
		}

		// Events
		// =====================================================================

		onSearchSelected (item) {
			this.value = item;
		}

		async onMapChange (latLng) {
			switch (this.config.geoService) {
				case GeoService.Nominatim:
					this.value = await this.geo.reverseNominatim(latLng);
					break;
				case GeoService.Mapbox:
					this.value = await this.geo.reverseMapbox(latLng);
					break;
				case GeoService.GoogleMaps:
					this.value = await this.geo.reverseGoogle(latLng);
					break;
				case GeoService.AppleMapKit:
					this.value = await this.geo.reverseApple(latLng);
					break;
				default:
					throw new Error('Unknown geo service: ' + this.config.geoService);
			}
		}

		onZoom (zoom) {
			this.value.zoom = zoom;
		}

		onPartChange ({ name, value }) {
			this.value.parts[name] = value;
			this.value.address =
				Object.values(this.value.parts).filter(Boolean).join(', ');
		}

	}
</script>

<style lang="less" module>
</style>
