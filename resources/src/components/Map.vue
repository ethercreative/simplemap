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
		}

		// Events
		// =====================================================================

		@Watch('latLng', { deep: true })
		onLatChange () {
			this.map.flyTo(this.latLng);
		}

		// Helpers
		// =====================================================================

		_googleMutant () {
			L.gridLayer.googleMutant({
				type: this.tiles.split('.')[1],
			}).addTo(this.map);
		}

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
