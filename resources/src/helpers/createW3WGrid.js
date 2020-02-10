export default function createW3WGrid (L, map) {
	let gridLayer;

	return function drawGrid () {
		const zoom = map.getZoom();
		const loadFeatures = zoom > 17;

		if (loadFeatures) { // Zoom level is high enough
			const ne = map.getBounds().getNorthEast();
			const sw = map.getBounds().getSouthWest();

			// Call the what3words Grid API to obtain the grid squares within the current visble bounding box
			window.what3words.api
				.gridSectionGeoJson({
					southwest: {
						lat: sw.lat,
						lng: sw.lng
					},
					northeast: {
						lat: ne.lat,
						lng: ne.lng
					}
				}).then(data => {
				// If the grid layer is already present, remove it as it will need to be replaced by the new grid section
				if (gridLayer)
					map.removeLayer(gridLayer);

				// Create a new GeoJSON layer, based on the GeoJSON returned from the what3words API
				gridLayer = L.geoJSON(data, {
					style: () => ({
						color: '#777',
						stroke: true,
						weight: 0.5
					}),
				}).addTo(map);
				// eslint-disable-next-line
			}).catch(console.error);
		} else if (gridLayer) {
			// If the grid layer already exists, remove it as the zoom level no longer requires the grid to be displayed
			map.removeLayer(gridLayer);
		}
	};
}
