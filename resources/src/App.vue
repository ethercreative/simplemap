<template>
	<div :class="cls">
		<div>
			<Search
				v-if="!config.hideSearch"
				:service="config.geoService"
				:default-value="val.address"
				:geo="geo"
				@selected="onSearchSelected"
				@clear="onClear"
				:has-map="!config.hideMap"
				:size="config.size"
			/>

			<Map
				v-if="!config.hideMap"
				:tiles="config.mapTiles"
				:token="config.mapToken"
				:latLng="{ lat: val.lat, lng: val.lng }"
				:zoom="+val.zoom"
				:min-zoom="config.minZoom"
				:max-zoom="config.maxZoom"
				@change="onMapChange"
				@zoom="onZoom"
				:has-search="!config.hideSearch"
				:has-address="!config.isSettings"
				:size="config.size"
			/>
		</div>

		<Address
			v-if="!config.isSettings"
			:hide="config.hideAddress"
			:has-search="!config.hideSearch"
			:has-map="!config.hideMap"
			:showLatLng="config.showLatLng"
			:size="config.size"
			:value="val"
			@changed="onPartChange"
		/>

		<input
			type="hidden"
			:name="config.name"
			:value="JSON.stringify(value)"
			v-if="!config.isSettings"
		/>

		<Fragment v-if="config.isSettings">
			<input
				type="hidden"
				:name="config.name.replace('__settings__', 'lat')"
				:value="value.lat"
			/>
			<input
				type="hidden"
				:name="config.name.replace('__settings__', 'lng')"
				:value="value.lng"
			/>
			<input
				type="hidden"
				:name="config.name.replace('__settings__', 'zoom')"
				:value="value.zoom"
			/>
		</Fragment>
	</div>
</template>

<script lang="js">
	import Search from './components/Search';
	import Address from './components/Address';
	import Map from './components/Map';
	import Geo from './common/Geo';
	import GeoService from './enums/GeoService';
	import Parts from './models/Parts';
	import Fragment from './components/Fragment';
	import PartsLegacy from './models/PartsLegacy';

	export default {
		props: {
			options: String,
		},

		components: {
			Search,
			Address,
			Map,
			Fragment,
		},

		data () {
			return {
				config: {
					isSettings: false,
					name: '',
					hideSearch: false,
					hideMap: false,
					hideAddress: false,
					showLatLng: false,
					minZoom: 3,
					maxZoom: 20,
					mapTiles: 'wikimedia',
					mapToken: '',
					geoService: 'nominatim',
					geoToken: '',
					locale: 'en',
					size: 'large',
				},

				value: {
					address: '',
					zoom: 15,
					lat: null,
					lng: null,
					parts: new Parts(),
				},

				geo: null,

				fullAddressDirty: false,

				defaultValue: null,
			};
		},

		created () {
			const { config, value, defaultValue } = JSON.parse(this.options);

			const isGoogle = config.geoService === GeoService.GoogleMaps;

			this.config = config;

			this.value = value;
			this.value.parts = isGoogle
				? new PartsLegacy(value.parts)
				: Parts.from(value.parts);

			this.defaultValue = defaultValue;
			this.defaultValue.parts = isGoogle
				? new PartsLegacy()
				: new Parts();

			this.geo = new Geo(config);
		},

		computed: {
			cls () {
				if (this.config.hideMap)
					return null;

				if (this.config.size === 'medium')
					return this.$style.medium;

				return null;
			},
			val () {
				return this.value.lat === null ? this.defaultValue : this.value;
			},
		},

		methods: {
			onSearchSelected (item) {
				this.value = {
					...this.value,
					...item,
				};
			},

			async onMapChange (latLng) {
				const zoom = this.value.zoom;

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
					case GeoService.Here:
						this.value = await this.geo.reverseHere(latLng);
						break;
					default:
						throw new Error('Unknown geo service: ' + this.config.geoService);
				}

				this.value.zoom       = zoom;
				this.fullAddressDirty = false;
			},

			onZoom (zoom) {
				this.value.zoom = zoom;
			},

			onPartChange ({ name, value }) {
				if (name === 'fullAddress') {
					this.value.address    = value;
					this.fullAddressDirty = value !== '';
				} else if (name === 'lat' || name === 'lng') {
					this.value[name] = value;
				} else {
					this.value.parts[name] = value;

					if (this.value.address === '' || !this.fullAddressDirty) {
						const parts = [];
						const keys = Object.keys(this.value.parts);

						for (let i = 0, l = keys.length; i < l; i++) {
							const k = keys[i];

							// Filter out guff google properties
							if (['number', 'address', 'city', 'postcode', 'county', 'state', 'country'].indexOf(k) === -1)
								continue;

							parts.push(this.value.parts[k]);
						}

						this.value.address = parts.filter(Boolean).join(', ');
					}
				}
			},

			onClear () {
				this.value = {
					address: '',
					zoom: 15,
					lat: null,
					lng: null,
					parts: new Parts(),
				};
			},
		},
	};
</script>

<style lang="less" module>
	.medium {
		display: flex;
		align-items: stretch;

		> * {
			width: 50%;
		}
	}
</style>
