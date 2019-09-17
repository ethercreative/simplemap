<template>
	<div>
		<div :class="wrapCls" ref="field">
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
					@open-offset="onResultsOpenOffset"
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
					@clear="onClear"
					:open-offset="resultsOpenOffset"
				/>
			</div>
		</div>

		<div v-if="config.size === 'mini'" :class="$style.mini">
			<span :class="value.address === '' && $style.empty">{{ value.address || emptyLabel }}</span>
			<button
				@click="onEditClick"
				type="button"
				ref="btn"
				class="btn"
			>Edit</button>
		</div>

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
	import Geo from './common/Geo';
	import GeoService from './enums/GeoService';
	import Parts from './models/Parts';
	import Fragment from './components/Fragment';
	import PartsLegacy from './models/PartsLegacy';
	import Search from './components/Search';
	import Address from './components/Address';
	import Map from './components/Map';
	import { t } from './filters/craft';

	const MapHud = window.Garnish.HUD.extend();

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
				emptyLabel: t('No address selected'),

				hud: null,
				resultsOpenOffset: 0,
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

		mounted () {
			if (this.config.size === 'mini')
				this.$refs.field.style.display = 'none';
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
			onEditClick () {
				if (this.hud) {
					this.hud.show();
					return;
				}

				let minBodyWidth;

				if (this.config.hideMap) {
					minBodyWidth = Math.min(626, window.innerWidth * 0.9);
				} else {
					minBodyWidth = Math.min(1300 - 48, window.innerWidth * 0.9);
				}

				this.$refs.field.style.display = 'block';
				this.hud = new MapHud(this.$refs.btn, this.$refs.field, {
					minBodyWidth,
				});
			},

			onResultsOpenOffset (value) {
				this.resultsOpenOffset = value;
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
		min-height: 250px;

		overflow: hidden;

		@media only screen and (max-width: 767px) {
			margin: 0 -12px;
		}

		:global(.hud) & {
			margin: -24px !important;
		}

		:global(.matrixblock) & {
			margin: 0 -14px !important;
		}

		:global(.superTable-layout-table) &,
		:global(.superTable-layout-row) & {
			margin: -4px -10px !important;
		}

		&.no-map {
			min-height: 0;
			margin: 0;
			overflow: visible;

			.content {
				padding: 0;

				:global(.hud) & {
					width: 100%;
					padding: 24px;
				}
			}
		}
	}

	.mini {
		display: flex;
		align-items: center;
		justify-content: space-between;

		:global(.btn) {
			margin-left: 24px;
			font-size: 14px;
		}
	}

	.empty {
		opacity: 0.5;
	}

	.content {
		position: relative;
		z-index: 2;
		box-sizing: border-box;
		width: 50%;
		padding: 24px;

		pointer-events: none;

		:global(.matrixblock) &,
		:global(.superTable-layout-table) &,
		:global(.superTable-layout-row) & {
			padding: 14px;
		}

		@media only screen and (max-width: 767px) {
			width: 100%;
			padding: 12px;
		}

		& > * {
			pointer-events: all;
		}
	}
</style>
