<?php

return [
	'Map' => 'Térkép',

	// Settings
	'Google Maps API Key' => 'Google Maps API kulcs',
	'Alternate Server API Key' => 'Alternatív API kulcs',
	'<a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">Get an API key.</a>'
		=> '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">API kulcs beszerzése</a>',
	'If you are using the above API key publically and need to add restrictions to it, you will need to pass an unrestricted API key here for exclusive use by the server. <a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">Get an API key.</a>'
		=> 'Amennyiben korlátozásokat állítasz be a publikus API kulcsra, ide egy korlátozások nélkülit kell megadnod az admin felület számára <a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">API kulcs beszerzése</a>',

	// Field settings
	'Latitude' => 'Latitude',
	'The maps default latitude' => 'Alapértelmezett szélesség',

	'Longitude' => 'Longitude',
	'The maps default longitude' => 'Alapértelmezett hosszúság',

	'Zoom Level' => 'Zoom',
	'The default zoom level' => 'Alapértelmezett zoom',

	'Height' => 'Magasság',
	'The maps height in the input' => 'A térkép magassága (admin)',

	'Hide Map' => 'Térkép elrejtése',
	'When on, the map will be hidden leaving just the address search field'
		=> 'Ha bekapcsolod, csak a cím beviteli mező fog látszani, a térkép nem.',

	'Hide Lat/Lng' => 'Szé./Hossz. elrejtése',
	'When on, the latitude & longitude fields will be hidden'
		=> 'Ha bekapcsolod, a beviteli mezők nem fognak látszani.',

	'Restrict by Country' => 'Korlátozás ország szerint',
	'Restrict the address search to a specific country'
		=> 'Csak bizonyos ország címei kereshetők majd',

	'Restrict by Type' => 'Korlátozás típus szerint',
	'Restrict the address search to a specific type'
		=> 'Csak bizonyos típusú címek kereshetők majd',

	'North-east Corner' => 'ÉK sarok',
	'South-west Corner' => 'DNY sarok',

	'Boundary Restriction' => 'Korlátozás terület szerint',
	'Restrict the address search to within a specific rectangular boundary'
		=> 'Csak a megadott pontok által határolt területen belül lehet majd keresni',

	'Configure Map' => 'Térkép beállítása',
	'Move, zoom, and resize the map' => 'Mozgasd, zoom-olj vagy méretezd át',

	// Field validation
	'Missing Lat/Lng' => 'Hiányzó Szé./Hossz.',
	'Missing Lat/Lng or valid address' => 'Hiányzó Szé./Hossz. vagy cím',
];
