<template>
	<div ref="map" :class="$style.map"></div>
</template>

<script lang="js">
	import { Component, Vue, Watch } from 'vue-property-decorator';
	import L from 'leaflet';
	// TODO: Only load mutants in if they're needed
	import 'leaflet.gridlayer.googlemutant';
	import 'leaflet.mapkitmutant';
	import 'leaflet/dist/leaflet.css';
	import MapTiles from '../enums/MapTiles';

	@Component({
		props: {
			tiles: String,
			token: String,
			latLng: Object,
			zoom: Number,
		}
	})
	export default class Map extends Vue {

		// Properties
		// =====================================================================

		map = null;
		marker = null;

		// Getters
		// =====================================================================

		get tileLayer () {
			const scale = L.Browser.retina ? '@2x.png' : '.png'
				, style = this.tiles.split('.')[1];

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
						attr: '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://www.mapbox.com/">Mapbox</a>',
					};
				default:
					throw new Error('Unknown map tiles service: ' + this.tiles);
			}
		}

		get icon () {
			return L.divIcon({
				html: '<svg xmlns="http://www.w3.org/2000/svg" width="23.5" height="41" viewBox="0 0 47 82"><path fill="#E7433B" d="M23.5036141,0 C10.5440829,0 0,10.5437082 0,23.5027789 C0,24.4175793 0.0650869313,25.3179165 0.159101388,26.1423217 C0.867825751,35.0299879 5.03338935,41.3938173 9.43760504,48.1336911 C15.1833347,56.9164988 21.6920278,62.0913384 21.6920278,80.1920939 C21.6920278,81.1900581 22.5019985,82 23.4999981,82 C24.4979978,82 25.3079685,81.1900581 25.3079685,80.1920939 C25.3079685,62.0949542 31.8166616,56.9201146 37.5623912,48.1336911 C41.9702229,41.3938173 46.1321705,35.0299879 46.833663,26.2074063 C46.9385253,25.3179165 46.9999963,24.4175793 46.9999963,23.499163 C47.0072282,10.5437082 36.4631453,0 23.5036141,0 Z M23,33 C18.0392,33 14,28.9608 14,24 C14,19.0392 18.0392,15 23,15 C27.9608,15 32,19.0392 32,24 C32,28.9608 27.9608,33 23,33 Z"/></svg>',
				iconSize: [23.5, 41],
				iconAnchor: [11.75, 41],
				className: '',
			});
		}

		// Vue
		// =====================================================================

		mounted () {
			this.map = L.map(this.$refs.map, {
				minZoom: 3,
			}).setView(this.latLng, this.zoom);

			if (this.tiles.indexOf('google') > -1) {
				this._googleMutant();
			} else if (this.tiles.indexOf('mapkit') > -1) {
				this._mapKitMutant();
			} else {
				const tileLayer = L.tileLayer(
					this.tileLayer.url,
					{ attribution: this.tileLayer.attr }
				);
				this.map.addLayer(tileLayer);
			}

			this.map.on('zoom', this.onZoom);

			this.setMarker();
		}

		// Actions
		// =====================================================================

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
		}

		// Events
		// =====================================================================

		/**
		 * Watches the latLng prop for changes, then updates the map location
		 * accordingly
		 */
		@Watch('latLng', { deep: true })
		onLatChange () {
			this.map.flyTo(this.latLng);
			this.setMarker();
		}

		/**
		 * Listens to map zoom event and triggers component zoom event.
		 */
		onZoom () {
			this.$emit('zoom', this.map.getZoom());
		}

		// Helpers
		// =====================================================================

		/**
		 * Sets up Leaflet to use Google Maps
		 *
		 * @private
		 */
		_googleMutant () {
			L.gridLayer.googleMutant({
				type: this.tiles.split('.')[1],
			}).addTo(this.map);
		}

		/**
		 * Sets up Leaflet to use Apple MapKit
		 *
		 * @private
		 */
		_mapKitMutant () {
			L.mapkitMutant({
				type: this.tiles.split('.')[1],
				authorizationCallback: done => done(this.token),
				language: window.Craft.language,
			}).addTo(this.map);
		}

	}
</script>

<style lang="less" module>
	.map {
		position: relative;
		z-index: 0;

		width: 100%;
		height: 450px;
		margin-top: 10px;
		box-sizing: border-box;

		border: 1px solid #e0e2e4;
		border-radius: 2px 2px 0 0;
	}
</style>
