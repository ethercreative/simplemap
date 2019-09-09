<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

return [

	// Field
	// =========================================================================

	'Map' => 'Map',
	'Geo-Coding' => 'Geo-Coding',

	'Search for a location' => 'Search for a location',
	'Clear address' => 'Clear address',

	'Full Address' => 'Full Address',
	'Name / Number' => 'Name / Number',
	'Street Address' => 'Street Address',
	'Town / City' => 'Town / City',
	'Postcode' => 'Postcode',
	'County' => 'County',
	'State' => 'State',
	'Country' => 'Country',

	'Latitude' => 'Latitude',
	'Longitude' => 'Longitude',

	'Zoom In' => 'Zoom In',
	'Zoom Out' => 'Zoom Out',
	'Center on Marker' => 'Center on Marker',

	// Field: Settings
	// -------------------------------------------------------------------------

	'Initial Location' => 'Initial Location',
	'The initial location and zoom that will show in the map field' =>
		'The initial location and zoom that will show in the map field',

	'Hide Search' => 'Hide Search',
	'Hide the location search field' => 'Hide the location search field',

	'Hide Map' => 'Hide Map',
	'Hide the map' => 'Hide the map',

	'Hide Address' => 'Hide Address',
	'Hide the address fields' => 'Hide the address fields',

	'Show Latitude / Longitude' => 'Show Latitude / Longitude',
	'Show the latitude / longitude fields' => 'Show the latitude / longitude fields',

	'Field Size' => 'Field Size',
	'Choose the size of the field to display' => 'Choose the size of the field to display',
	'Normal' => 'Normal',
	'Mini' => 'Mini',

	'All Countries' => 'All Countries',
	'Preferred Country' => 'Preferred Country',
	'When searching for a location, results in this country will take precedence. Be aware that some services will show results ONLY within this country.' =>
		'When searching for a location, results in this country will take precedence. Be aware that some services will show results ONLY within this country.',

	// Settings
	// =========================================================================

	'Select your map style' => 'Select your map style',
	'Map Tiles' => 'Map Tiles',
	'Select the style of map tiles.' => 'Select the style of map tiles.',

	'Map Token' => 'Map Token',
	'Add the API key for map tiles service you are using.' =>
		'Add the API key for map tiles service you are using.',

	'Geo Service' => 'Geo Service',
	'Select the service to be used for Geocoding.' =>
		'Select the service to be used for Geocoding.',

	'Geo Token' => 'Geo Token',
	'Add the API key for the geocoding service.' =>
		'Add the API key for the geocoding service.',

	'Private Key' => 'Private Key',
	'Paste the contents of your private key files below.' => 'Paste the contents of your private key files below.',

	'Key ID' => 'Key ID',
	'The ID of the key associated with your private key.' => 'The ID of the key associated with your private key.',

	'Team ID' => 'Team ID',
	'The team ID that created the key ID and private key.' => 'The team ID that created the key ID and private key.',

	'Notice' => 'Notice',
	'MapKit does not support individual address parts.' => 'MapKit does not support individual address parts.',

	'App ID' => 'App ID',
	'Your Here app ID.' => 'Your Here app ID.',

	'App Code' => 'App Code',
	'Your Here app code.' => 'Your Here app code.',

	'Geolocation Service' => 'Geolocation Service',
	'Select the service to be used for Geolocating users.' => 'Select the service to be used for Geolocating users.',

	'Geolocation Token' => 'Geolocation Token',
	'Add the API key for the geolocation service.' => 'Add the API key for the geolocation service.',

	// Settings: Map Tiles Options
	// -------------------------------------------------------------------------

	'Open Source' => 'Open Source',

	'Wikimedia' => 'Wikimedia',

	'OpenStreetMap' => 'OpenStreetMap',

	'Carto: Voyager' => 'Carto: Voyager',
	'Carto: Positron' => 'Carto: Positron',
	'Carto: Dark Matter' => 'Carto: Dark Matter',

	'Requires API Key (Token)' => 'Requires API Key (Token)',

	'Mapbox: Outdoors' => 'Mapbox: Outdoors',
	'Mapbox: Streets' => 'Mapbox: Streets',
	'Mapbox: Light' => 'Mapbox: Light',
	'Mapbox: Dark' => 'Mapbox: Dark',

	'Google Maps: Roadmap' => 'Google Maps: Roadmap',
	'Google Maps: Terrain' => 'Google Maps: Terrain',
	'Google Maps: Hybrid' => 'Google Maps: Hybrid',

	'Apple MapKit: Standard' => 'Apple MapKit: Standard',
	'Apple MapKit: Muted Standard' => 'Apple MapKit: Muted Standard',
	'Apple MapKit: Satellite' => 'Apple MapKit: Satellite',
	'Apple MapKit: Hybrid' => 'Apple MapKit: Hybrid',

	'Here: Normal Day' => 'Here: Normal Day',
	'Here: Normal Day Grey' => 'Here: Normal Day Grey',
	'Here: Normal Day Transit' => 'Here: Normal Day Transit',
	'Here: Reduced' => 'Here: Reduced',
	'Here: Pedestrian' => 'Here: Pedestrian',
	'Here: Terrain' => 'Here: Terrain',
	'Here: Satellite' => 'Here: Satellite',
	'Here: Hybrid' => 'Here: Hybrid',

	// Settings: Geo Service Options
	// -------------------------------------------------------------------------

	'Nominatim' => 'Nominatim',
	'Mapbox' => 'Mapbox',
	'Google Maps' => 'Google Maps',
	'Apple MapKit' => 'Apple MapKit',
	'Here' => 'Here',

	// Settings: Geo Location Services
	// -------------------------------------------------------------------------

	'None' => 'None',
	'ipstack' => 'ipstack',
	'MaxMind (Lite, ~60MB download)' => 'MaxMind (Lite, ~60MB download)',
	'MaxMind' => 'MaxMind',

	// Settings: Info
	// -------------------------------------------------------------------------

	'Getting API Keys' => 'Getting API Keys',
	'You will need to enable the **Maps Javascript API** and **Places API** for if using Google for the map tiles, and the **Places API** and **Geocoding API** if you are using it for the Geo service.' =>
		'You will need to enable the **Maps Javascript API** and **Places API** for if using Google for the map tiles, and the **Places API** and **Geocoding API** if you are using it for the Geo service.',
	'You can use the same key for both map tiles and geo service, no configuration needed!' =>
		'You can use the same key for both map tiles and geo service, no configuration needed!',
	'We currently only support Apple MapKit for map tiles only.' =>
		'We currently only support Apple MapKit for map tiles only.',

	'Getting Geolocation API Keys' => 'Getting Geolocation API Keys',
	'ipstack offer free and paid-for versions of their API.' => 'ipstack offer free and paid-for versions of their API.',
	'MaxMind offer free lookup database that must be stored locally, and a paid-for version of their API.' =>
		'MaxMind offer free lookup database that must be stored locally, and a paid-for version of their API.',

];
