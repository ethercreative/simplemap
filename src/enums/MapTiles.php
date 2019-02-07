<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\enums;

use ether\simplemap\SimpleMap;

/**
 * Class MapTiles
 *
 * @author  Ether Creative
 * @package ether\simplemap\enums
 */
abstract class MapTiles
{

	// Consts
	// =========================================================================

	// Open Source
	// -------------------------------------------------------------------------

	// Wikimedia
	// https://foundation.wikimedia.org/wiki/Maps_Terms_of_Use
	const Wikimedia = 'wikimedia';

	// OpenStreetMaps
	// https://operations.osmfoundation.org/policies/tiles/
	const OpenStreetMap = 'openstreetmap';

	// Carto
	// https://github.com/CartoDB/basemap-styles
	const CartoVoyager = 'carto.rastertiles/voyager';
	const CartoPositron = 'carto.light_all';
	const CartoDarkMatter = 'carto.dark_all';

	// Requires API Key (Token)
	// -------------------------------------------------------------------------

	// Mapbox
	const MapboxOutdoors = 'mapbox.outdoors';
	const MapboxStreets = 'mapbox.streets';
	const MapboxLight = 'mapbox.light';
	const MapboxDark = 'mapbox.dark';

	// Google Maps
	const GoogleRoadmap = 'google.roadmap';
	const GoogleTerrain = 'google.terrain';
	const GoogleHybrid = 'google.hybrid';

	// Apple MapKit
	const MapKitStandard      = 'mapkit.standard';
	const MapKitMutedStandard = 'mapkit.muted';
	const MapKitSatellite     = 'mapkit.satellite';
	const MapKitHybrid        = 'mapkit.hybrid';

	// Methods
	// =========================================================================

	public static function getSelectOptions ()
	{
		return [
			['optgroup' => SimpleMap::t('Open Source')],

			self::Wikimedia => SimpleMap::t('Wikimedia'),

			self::OpenStreetMap => SimpleMap::t('OpenStreetMap'),

			self::CartoVoyager => SimpleMap::t('Carto: Voyager'),
			self::CartoPositron => SimpleMap::t('Carto: Positron'),
			self::CartoDarkMatter => SimpleMap::t('Carto: Dark Matter'),

			['optgroup' => SimpleMap::t('Requires API Key (Token)')],

			self::MapboxOutdoors => SimpleMap::t('Mapbox: Outdoors'),
			self::MapboxStreets => SimpleMap::t('Mapbox: Streets'),
			self::MapboxLight => SimpleMap::t('Mapbox: Light'),
			self::MapboxDark => SimpleMap::t('Mapbox: Dark'),

			self::GoogleRoadmap => SimpleMap::t('Google Maps: Roadmap'),
			self::GoogleTerrain => SimpleMap::t('Google Maps: Terrain'),
			self::GoogleHybrid => SimpleMap::t('Google Maps: Hybrid'),

			self::MapKitStandard       => SimpleMap::t('Apple MapKit: Standard'),
			self::MapKitMutedStandard  => SimpleMap::t('Apple MapKit: Muted Standard'),
			self::MapKitSatellite      => SimpleMap::t('Apple MapKit: Satellite'),
			self::MapKitHybrid         => SimpleMap::t('Apple MapKit: Hybrid'),
		];
	}

}