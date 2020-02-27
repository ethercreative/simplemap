<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2020 Ether Creative
 */

namespace ether\simplemap\services;

use ether\simplemap\SimpleMap;
use What3words\Geocoder\Geocoder;

/**
 * Class What3WordsService
 *
 * @author  Ether Creative
 * @package ether\simplemap\services
 */
class What3WordsService
{

	public static function convertLatLngToW3W ($lat, $lng)
	{
		return self::_geocoder()->convertTo3wa($lat, $lng)['words'];
	}

	private static function _geocoder ()
	{
		static $geocoder;

		if ($geocoder)
			return $geocoder;

		return $geocoder = new Geocoder(SimpleMap::getInstance()->getSettings()->getW3WToken());
	}

}
