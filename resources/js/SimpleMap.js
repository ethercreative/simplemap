import Mapbox from "./maps/Mapbox";
import OpenStreetMap from "./maps/OpenStreetMap";

class SimpleMap {

	// Properties
	// =========================================================================

	map = null;

	constructor (type = "osm", apiKey = null) {
		switch (type) {
			case "mapbox":
				this.map = new Mapbox(apiKey);
				break;
			case "osm":
				this.map = new OpenStreetMap();
				break;
		}
	}

	// Actions
	// =========================================================================

	/**
	 * Creates the map
	 *
	 * @param {Element} el - The target dom element to instantiate the map on
	 * @param {{center:{lat:number, lng:number}, zoom: number}} start
	 */
	create (el, start) {
		this.map.create(el, start);
	}

}

window.SimpleMap = SimpleMap;