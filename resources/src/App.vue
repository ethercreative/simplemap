<template>
	<div :class="wrapCls">
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
			:hide-search="config.hideSearch"
		/>

		<div :class="$style.content">
			<Search
				v-if="!config.hideSearch"
				:service="config.geoService"
				:default-value="val.address"
				:geo="geo"
				@selected="onSearchSelected"
				@is-open="onResultsOpen"
				:has-map="!config.hideMap"
			/>

			<Address
				v-if="!config.isSettings"
				:hide="config.hideAddress"
				:has-search="!config.hideSearch"
				:has-map="!config.hideMap"
				:showLatLng="config.showLatLng"
				:value="val"
				@changed="onPartChange"
				:results-open="resultsOpen"
			/>
		</div>

		<!--<button
			class="btn"
			@click="onClear()"
			type="button"
		>
			{{labels.clear}}
		</button>-->

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
	import { t } from './filters/craft';

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

				labels: {
					clear: t('Clear address'),
				},

				resultsOpen: false,
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
			wrapCls () {
				const cls = [this.$style.wrap];

				if (this.config.hideMap)
					cls.push(this.$style['no-map']);

				return cls;
			},

			val () {
				return this.value.lat === null ? this.defaultValue : this.value;
			},
		},

		methods: {
			onResultsOpen (value) {
				this.resultsOpen = value;
			},

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
	.wrap {
		position: relative;
		margin: 0 -24px;
		min-height: 360px;

		@media only screen and (max-width: 767px) {
			margin: 0 -12px;
		}

		&.no-map {
			min-height: 0;
			margin: 0;

			.content {
				padding: 0;
			}
		}
	}
	.content {
		position: relative;
		z-index: 2;
		box-sizing: border-box;
		padding: 24px;
		width: 50%;

		pointer-events: none;

		@media only screen and (max-width: 767px) {
			padding: 12px;
			width: 100%;
		}

		& > * {
			pointer-events: all;
		}
	}
</style>
