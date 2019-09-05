<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\enums;

use ether\simplemap\models\Settings;
use ether\simplemap\SimpleMap;
use ether\simplemap\services\GeoService;

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
		$isLite = SimpleMap::v(SimpleMap::EDITION_LITE);

		return [
			['optgroup' => SimpleMap::t('Open Source')],

			self::Wikimedia => SimpleMap::t('Wikimedia'),

			self::OpenStreetMap => SimpleMap::t('OpenStreetMap'),

			self::CartoVoyager    => SimpleMap::t('Carto: Voyager'),
			self::CartoPositron   => SimpleMap::t('Carto: Positron'),
			self::CartoDarkMatter => SimpleMap::t('Carto: Dark Matter'),

			['optgroup' => SimpleMap::t('Requires API Key (Token)')],

			self::GoogleRoadmap => SimpleMap::t('Google Maps: Roadmap'),
			self::GoogleTerrain => SimpleMap::t('Google Maps: Terrain'),
			self::GoogleHybrid  => SimpleMap::t('Google Maps: Hybrid'),

			self::MapboxOutdoors => self::pro('Mapbox: Outdoors', $isLite),
			self::MapboxStreets  => self::pro('Mapbox: Streets', $isLite),
			self::MapboxLight    => self::pro('Mapbox: Light', $isLite),
			self::MapboxDark     => self::pro('Mapbox: Dark', $isLite),

			self::MapKitStandard       => self::pro('Apple MapKit: Standard', $isLite),
			self::MapKitMutedStandard  => self::pro('Apple MapKit: Muted Standard', $isLite),
			self::MapKitSatellite      => self::pro('Apple MapKit: Satellite', $isLite),
			self::MapKitHybrid         => self::pro('Apple MapKit: Hybrid', $isLite),

			self::HereNormalDay        => self::pro('Here: Normal Day', $isLite),
			self::HereNormalDayGrey    => self::pro('Here: Normal Day Grey', $isLite),
			self::HereNormalDayTransit => self::pro('Here: Normal Day Transit', $isLite),
			self::HereReduced          => self::pro('Here: Reduced', $isLite),
			self::HerePedestrian       => self::pro('Here: Pedestrian', $isLite),
			self::HereTerrain          => self::pro('Here: Terrain', $isLite),
			self::HereSatellite        => self::pro('Here: Satellite', $isLite),
			self::HereHybrid           => self::pro('Here: Hybrid', $isLite),
		];
	}

	/**
	 * Get the tiles url for the given type and scale
	 *
	 * @param string $type
	 * @param int $scale
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function getTiles ($type, $scale = 1)
	{
		$scale = $scale == 1 ? '.png' : '@2x.png';
		$style = strpos($type, '.') !== false ? explode('.', $type, 2)[1] : '';

		switch ($type)
		{
			case self::Wikimedia:
				return [
					'url' => 'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}' . $scale,
					'size' => 512,
				];
			case self::OpenStreetMap:
				return [
					'url' => 'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
					'size' => 256,
				];
			case self::CartoVoyager:
			case self::CartoPositron:
			case self::CartoDarkMatter:
				return [
					'url' => 'https://a.basemaps.cartocdn.com/' . $style . '/{z}/{x}/{y}' . $scale,
					'size' => 256,
				];
		}

		throw new \Exception('Unknown tile type "' . $type . '"');
	}

	// Helpers
	// =========================================================================

	public static function pro ($label, $isLite)
	{
		return [
			'label'    => SimpleMap::t($label) . ($isLite ? ' (Pro)' : ''),
			'disabled' => $isLite,
		];
	}

}
