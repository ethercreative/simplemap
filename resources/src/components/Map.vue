<template>
	<div :class="cls"></div>
</template>

<script lang="js">
	import L from 'leaflet';
	// TODO: Only load mutants in if they're needed
	import 'leaflet.gridlayer.googlemutant';
	import 'leaflet.mapkitmutant';
	import MapTiles from '../enums/MapTiles';
	import 'leaflet/dist/leaflet.css';
	import waitForGlobal from '../helpers/waitForGlobal';

	export default {
		props: {
			tiles: String,
			token: [String, Object],
			latLng: Object,
			zoom: Number,
			minZoom: Number,
			maxZoom: Number,
			hasSearch: Boolean,
			hasAddress: Boolean,
		},

		data () {
			return {
				map: null,
				marker: null,
			};
		},

		mounted () {
			this.map = L.map(this.$el, {
				minZoom: this.minZoom,
				maxZoom: this.maxZoom,
				scrollWheelZoom: false,
			}).setView(this.latLng, this.zoom);

			if (this.tiles.indexOf('google') > -1) {
				this._googleMutant();
			} else if (this.tiles.indexOf('mapkit') > -1) {
				this._mapKitMutant();
			} else {
				const opts = { attribution: this.tileLayer.attr };

				if (this.tileLayer.subdomains)
					opts.subdomains = this.tileLayer.subdomains;

				const tileLayer = L.tileLayer(
					this.tileLayer.url,
					opts
				);
				this.map.addLayer(tileLayer);
			}

			this.map.on('zoom', this.onZoom);

			this.setMarker();

			// Re-draw the map if it was hidden
			const io = new IntersectionObserver(entries => {
				if (entries[0].intersectionRatio <= 0)
					return;

				this.map.invalidateSize(true);
			});

			io.observe(this.$el);
		},

		computed: {
			cls () {
				const cls = [this.$style.map];

				if (!this.hasSearch && !this.hasAddress) cls.push(this.$style.alone);
				else if (!this.hasSearch) cls.push(this.$style.searchless);
				else if (!this.hasAddress) cls.push(this.$style.addressless);

				return cls;
			},

			tileLayer () {
				const scale     = L.Browser.retina ? '@2x.png' : '.png'
					, hereScale = L.Browser.retina ? '512' : '256'
					, style     = this.tiles.split(/\.(.+)/)[1];

				switch (this.tiles) {
					case MapTiles.Wikimedia:
						return {
							url: `https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}${scale}`,
							attr: '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://maps.wikimedia.org" target="_blank" rel="noreferrer">Wikimedia</a>',
						};
					case MapTiles.OpenStreetMap:
						return {
							url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
							attr: '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>',
						};
					case MapTiles.CartoVoyager:
					case MapTiles.CartoPositron:
					case MapTiles.CartoDarkMatter:
						return {
							url: `https://{s}.basemaps.cartocdn.com/${style}/{z}/{x}/{y}${scale}`,
							attr: '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://carto.com/attribution" target="_blank" rel="noreferrer">CARTO</a>',
						};
					case MapTiles.MapboxOutdoors:
					case MapTiles.MapboxStreets:
					case MapTiles.MapboxLight:
					case MapTiles.MapboxDark:
						return {
							url: `https://api.tiles.mapbox.com/v4/mapbox.${style}/{z}/{x}/{y}${scale}?access_token=${this.token}`,
							attr: '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://www.mapbox.com/" target="_blank" rel="noreferrer">Mapbox</a>',
						};
					case MapTiles.HereNormalDay:
					case MapTiles.HereNormalDayGrey:
					case MapTiles.HereNormalDayTransit:
					case MapTiles.HereReduced:
					case MapTiles.HerePedestrian:
						return {
							url: `https://{s}.base.maps.api.here.com/maptile/2.1/maptile/newest/${style}/{z}/{x}/{y}/${hereScale}/png8?app_id=${this.token.appId}&app_code=${this.token.appCode}`,
							attr: '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://here.com/" target="_blank" rel="noreferrer">Here</a>',
							subdomains: '1234',
						};
					case MapTiles.HereTerrain:
					case MapTiles.HereSatellite:
					case MapTiles.HereHybrid:
						return {
							url: `https://{s}.aerial.maps.api.here.com/maptile/2.1/maptile/newest/${style}/{z}/{x}/{y}/${hereScale}/png8?app_id=${this.token.appId}&app_code=${this.token.appCode}`,
							attr: '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://here.com/" target="_blank" rel="noreferrer">Here</a>',
							subdomains: '1234',
						};
					default:
						throw new Error('Unknown map tiles service: ' + this.tiles);
				}
			},

			icon () {
				return L.divIcon({
					html: '<svg xmlns="http://www.w3.org/2000/svg" width="23.5" height="41" viewBox="0 0 47 82"><path fill="#E7433B" d="M23.5036141,0 C10.5440829,0 0,10.5437082 0,23.5027789 C0,24.4175793 0.0650869313,25.3179165 0.159101388,26.1423217 C0.867825751,35.0299879 5.03338935,41.3938173 9.43760504,48.1336911 C15.1833347,56.9164988 21.6920278,62.0913384 21.6920278,80.1920939 C21.6920278,81.1900581 22.5019985,82 23.4999981,82 C24.4979978,82 25.3079685,81.1900581 25.3079685,80.1920939 C25.3079685,62.0949542 31.8166616,56.9201146 37.5623912,48.1336911 C41.9702229,41.3938173 46.1321705,35.0299879 46.833663,26.2074063 C46.9385253,25.3179165 46.9999963,24.4175793 46.9999963,23.499163 C47.0072282,10.5437082 36.4631453,0 23.5036141,0 Z M23,33 C18.0392,33 14,28.9608 14,24 C14,19.0392 18.0392,15 23,15 C27.9608,15 32,19.0392 32,24 C32,28.9608 27.9608,33 23,33 Z"/></svg>',
					iconSize: [23.5, 41],
					iconAnchor: [11.75, 41],
					className: '',
				});
			},
		},

		watch: {
			latLng: {
				deep: true,
				/**
				 * Watches the latLng prop for changes, then updates the map location
				 * accordingly
				 */
				handler (next, old) {
					if (next.lat === old.lat && next.lng === old.lng)
						return;

					this.map.panTo(this.latLng);
					this.setMarker();
				},
			},
		},

		methods: {
			setMarker () {
				if (this.marker)
					this.map.removeLayer(this.marker);

				this.marker = L.marker(this.latLng, {
					icon: this.icon,
					draggable: true,
					autoPan: true,
				});

				this.map.addLayer(this.marker);

				this.marker.on('dragend', () => {
					this.$emit('change', this.marker.getLatLng());
				});
			},

			/**
			 * Listens to map zoom event and triggers component zoom event.
			 */
			onZoom () {
				this.$emit('zoom', this.map.getZoom());
			},

			/**
			 * Sets up Leaflet to use Google Maps
			 *
			 * @private
			 */
			_googleMutant () {
				waitForGlobal('google', () => {
					L.gridLayer.googleMutant({
						type: this.tiles.split('.')[1],
					}).addTo(this.map);
				});
			},

			/**
			 * Sets up Leaflet to use Apple MapKit
			 *
			 * @private
			 */
			_mapKitMutant () {
				waitForGlobal('mapkit', () => {
					L.mapkitMutant({
						type: this.tiles.split('.')[1],
						authorizationCallback: done => done(this.token),
						language: window.Craft.language,
					}).addTo(this.map);
				});
			},
		},
	};
</script>

<style lang="less" module>
	.map {
		position: relative;
		z-index: 0;

		width: 100%;
		height: 450px;
		box-sizing: border-box;

		border: 1px solid #e0e2e4;

		&.searchless {
			border-radius: 2px 2px 0 0;
		}

		&.addressless {
			border-radius: 0 0 2px 2px;
		}

		&.alone {
			border-radius: 2px;
		}
	}
</style>
