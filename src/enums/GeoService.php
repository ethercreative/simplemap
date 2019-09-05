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
 * Class GeoService
 *
 * @author  Ether Creative
 * @package ether\simplemap\enums
 */
abstract class GeoService
{

	// Consts
	// =========================================================================

	// Open Source
	// -------------------------------------------------------------------------

	// https://operations.osmfoundation.org/policies/nominatim/
	const Nominatim = 'nominatim';

	// Requires API Key (Token)
	// -------------------------------------------------------------------------

	const Mapbox = 'mapbox';

	const GoogleMaps = 'google';

	const AppleMapKit = 'apple';

	const Here = 'here';

	// Helpers
	// =========================================================================

	public static function getSelectOptions ()
	{
		$isLite = SimpleMap::v(SimpleMap::EDITION_LITE);

		return [
			[ 'optgroup' => SimpleMap::t('Open Source') ],

			self::Nominatim => SimpleMap::t('Nominatim'),

			[ 'optgroup' => SimpleMap::t('Requires API Key (Token)') ],

			self::GoogleMaps => SimpleMap::t('Google Maps'),
			self::Mapbox => MapTiles::pro('Mapbox', $isLite),

			// MapKit lacks both separate address parts and country restriction
			// on the front-end, and any sort of server-side API, so it's
			// disabled for now.
//			self::AppleMapKit => MapTiles::pro('Apple MapKit', $isLite),

			self::Here => MapTiles::pro('Here', $isLite),
		];
	}

}
