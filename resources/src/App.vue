<template>
	<div>
		<Search
			v-if="!config.hideSearch"
			:name="config.name"
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
			:zoom="config.zoom"
			@change="onMapChange"
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
	import Geo from './common/Geo';

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

			this.geo = new Geo(config);
		}

		// Events
		// =====================================================================

		onSearchSelected (item) {
			this.value = item;
		}

		onMapChange (latLng) {
			console.log(latLng);
		}

	}
</script>

<style lang="less" module>
</style>
