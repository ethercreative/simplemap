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

	// Helpers
	// =========================================================================

	public static function getSelectOptions ()
	{
		return [
			[ 'optgroup' => SimpleMap::t('Open Source') ],

			self::Nominatim => SimpleMap::t('Nominatim'),

			[ 'optgroup' => SimpleMap::t('Requires API Key (Token)') ],

			self::Mapbox => SimpleMap::t('Mapbox'),
			self::GoogleMaps => SimpleMap::t('Google Maps'),

			// MapKit lacks both separate address parts on the front-end, and
			// any sort of server-side API, so it's disabled for now.
			//self::AppleMapKit => SimpleMap::t('Apple MapKit'),
		];
	}

}