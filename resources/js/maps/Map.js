export default class Map {

	/**
	 * Creates the map
	 *
	 * @param {Element} el - The target dom element to instantiate the map on
	 * @param {{center:{lat:number, lng:number}, zoom: number}} start
	 * @return Map
	 */
	create (el, start) {
		throw "Map.create not implemented";
		return this;
	}

	/**
	 * Centres the map on the given location
	 *
	 * @param {{lat:number,lng:number}} latLng - The location to centre on
	 */
	centre (latLng) {
		throw "Map.centre not implemented";
	}

}