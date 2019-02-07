const MapTiles = {
	// Open Source
	// -------------------------------------------------------------------------

	// Wikimedia
	// https://foundation.wikimedia.org/wiki/Maps_Terms_of_Use
	Wikimedia: 'wikimedia',

	// OpenStreetMaps
	// https://operations.osmfoundation.org/policies/tiles/
	OpenStreetMap: 'openstreetmap',

	// Carto
	// https://github.com/CartoDB/basemap-styles
	CartoVoyager: 'carto.rastertiles/voyager',
	CartoPositron: 'carto.light_all',
	CartoDarkMatter: 'carto.dark_all',

	// Requires API Key (Token)
	// -------------------------------------------------------------------------

	// Mapbox
	MapboxOutdoors: 'mapbox.outdoors',
	MapboxStreets: 'mapbox.streets',
	MapboxLight: 'mapbox.light',
	MapboxDark: 'mapbox.dark',

	// Google Maps
	GoogleRoadmap: 'google.roadmap',
	GoogleTerrain: 'google.terrain',
	GoogleHybrid: 'google.hybrid',

	// Apple MapKit
	MapKitStandard: 'mapkit.standard',
	MapKitMutedStandard: 'mapkit.muted',
	MapKitSatellite: 'mapkit.satellite',
	MapKitHybrid: 'mapkit.hybrid',
};

export default MapTiles;
