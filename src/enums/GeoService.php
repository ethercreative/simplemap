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
 * Class GeoService
 *
 * @author  Ether Creative
 * @package ether\maps\enums
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
		return [
			[ 'optgroup' => Maps::t('Open Source') ],

			self::Nominatim => Maps::t('Nominatim'),

			[ 'optgroup' => Maps::t('Requires API Key (Token)') ],

			self::Mapbox => Maps::t('Mapbox'),
			self::GoogleMaps => Maps::t('Google Maps'),

			// MapKit lacks both separate address parts and country restriction
			// on the front-end, and any sort of server-side API, so it's
			// disabled for now.
//			self::AppleMapKit => Maps::t('Apple MapKit'),

			self::Here => Maps::t('Here'),
		];
	}

}
