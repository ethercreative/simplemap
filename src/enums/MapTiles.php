<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\maps\enums;

use ether\maps\Maps;

/**
 * Class MapTiles
 *
 * @author  Ether Creative
 * @package ether\maps\enums
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

	// Here
	const HereNormalDay        = 'here.normal.day';
	const HereNormalDayGrey    = 'here.normal.day.grey';
	const HereNormalDayTransit = 'here.normal.day.transit';
	const HereReduced          = 'here.reduced.day';
	const HerePedestrian       = 'here.pedestrian.day';
	const HereTerrain          = 'here.terrain.day';
	const HereSatellite        = 'here.satellite.day';
	const HereHybrid           = 'here.hybrid.day';

	// Methods
	// =========================================================================

	public static function getSelectOptions ()
	{
		return [
			['optgroup' => Maps::t('Open Source')],

			self::Wikimedia => Maps::t('Wikimedia'),

			self::OpenStreetMap => Maps::t('OpenStreetMap'),

			self::CartoVoyager => Maps::t('Carto: Voyager'),
			self::CartoPositron => Maps::t('Carto: Positron'),
			self::CartoDarkMatter => Maps::t('Carto: Dark Matter'),

			['optgroup' => Maps::t('Requires API Key (Token)')],

			self::MapboxOutdoors => Maps::t('Mapbox: Outdoors'),
			self::MapboxStreets => Maps::t('Mapbox: Streets'),
			self::MapboxLight => Maps::t('Mapbox: Light'),
			self::MapboxDark => Maps::t('Mapbox: Dark'),

			self::GoogleRoadmap => Maps::t('Google Maps: Roadmap'),
			self::GoogleTerrain => Maps::t('Google Maps: Terrain'),
			self::GoogleHybrid => Maps::t('Google Maps: Hybrid'),

			self::MapKitStandard       => Maps::t('Apple MapKit: Standard'),
			self::MapKitMutedStandard  => Maps::t('Apple MapKit: Muted Standard'),
			self::MapKitSatellite      => Maps::t('Apple MapKit: Satellite'),
			self::MapKitHybrid         => Maps::t('Apple MapKit: Hybrid'),

			self::HereNormalDay        => Maps::t('Here: Normal Day'),
			self::HereNormalDayGrey    => Maps::t('Here: Normal Day Grey'),
			self::HereNormalDayTransit => Maps::t('Here: Normal Day Transit'),
			self::HereReduced          => Maps::t('Here: Reduced'),
			self::HerePedestrian       => Maps::t('Here: Pedestrian'),
			self::HereTerrain          => Maps::t('Here: Terrain'),
			self::HereSatellite        => Maps::t('Here: Satellite'),
			self::HereHybrid           => Maps::t('Here: Hybrid'),
		];
	}

}
