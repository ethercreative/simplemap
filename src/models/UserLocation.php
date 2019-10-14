<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use craft\helpers\Json;
use ether\simplemap\services\GeoService;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class UserLocation
 *
 * @author  Ether Creative
 * @package ether\simplemap\models
 */
class UserLocation extends BaseLocation
{

	// Properties
	// =========================================================================

	/** @var string */
	public $ip;

	/** @var string */
	public $countryCode;

	/** @var bool */
	public $isEU;

	// Methods
	// =========================================================================

	/**
	 * Get the distance from this user location to the given location
	 *
	 * @param array  $to   - a lat/lng keyed array
	 * @param string $unit - the unit to measure by (either 'mi' (miles) or
	 *                     'km' (kilometers))
	 *
	 * @return float|int
	 * @throws InvalidConfigException
	 * @throws \Exception
	 */
	public function distance ($to, $unit = 'km')
	{
		// Normalize unit
		$unit = GeoService::normalizeDistance($unit);

		// Base Distance
		$distance = $unit === 'km' ? '111.045' : '69.0';

		// Coordinates
		$to = GeoService::normalizeLocation($to);

		if ($to === null)
			throw new InvalidConfigException('Invalid target lat/lng');

		$targetLat = $to['lat'];
		$targetLng = $to['lng'];

		return (
			$distance *
			rad2deg(
				acos(
					cos(deg2rad($this->lat)) *
					cos(deg2rad($targetLat)) *
					cos(deg2rad($this->lng) - deg2rad($targetLng)) +
					sin(deg2rad($this->lat)) *
					sin(deg2rad($targetLat))
				)
			)
		);
	}

}
