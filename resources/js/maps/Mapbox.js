/* global mapboxgl */

import Map from "./Map";

export default class Mapbox extends Map {

	// Properties
	// =========================================================================

	map = null;

	constructor (apiKey) {
		super();
		// TODO: Ensure the below are loaded
		/*
		<script src='https://api.mapbox.com/mapbox-gl-js/v0.47.0/mapbox-gl.js'></script>
		<link href='https://api.mapbox.com/mapbox-gl-js/v0.47.0/mapbox-gl.css' rel='stylesheet' />
		 */
		mapboxgl.accessToken = apiKey;
	}

	create (el, { center: { lat, lng }, zoom }) {
		this.map = new mapboxgl.Map({
			container: el,
			style: "mapbox://styles/mapbox/streets-v10",
			center: [lng, lat],
			zoom,
		});

		this.map.addControl(new mapboxgl.NavigationControl());

		return this;
	}

	centre ({ lat, lng }) {
		this.map.flyTo({
			center: [lng, lat],
		});
	}

}