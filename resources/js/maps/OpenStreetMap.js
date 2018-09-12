/* global L */

import Map from "./Map";

export default class OpenStreetMap extends Map {

	// Properties
	// =========================================================================

	map = null;

	constructor () {
		super();
		// TODO: Ensure the below are loaded
		/*
		 <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==" crossorigin=""/>
		 <script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js" integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA==" crossorigin=""></script>
		 */
	}

	create (el, { center, zoom }) {
		this.map = L.map(el);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
			center,
			zoom,
		}).addTo(this.map);

		return this;
	}

	centre (latLng) {
		this.map.flyTo(latLng);
	}

}