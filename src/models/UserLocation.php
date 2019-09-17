<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use craft\helpers\Json;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class UserLocation
 *
 * @author  Ether Creative
 * @package ether\simplemap\models
 */
class UserLocation
{

	// Properties
	// =========================================================================

	/** @var string */
	public $ip;

	/** @var float */
	public $lat;

	/** @var float */
	public $lng;

	/** @var string */
	public $address;

	/** @var string */
	public $countryCode;

	/** @var bool */
	public $isEU;

	/** @var Parts */
	public $parts;

	// Constructor
	// =========================================================================

	public function __construct ($config = [])
	{
		if (!empty($config))
			Yii::configure($this, $config);

		if ($this->address === null)
			$this->address = '';

		if ($this->parts === null)
		{
			$this->parts = new Parts();
		}
		else if (!($this->parts instanceof Parts))
		{
			if ($this->parts && !is_array($this->parts))
				$this->parts = Json::decodeIfJson($this->parts, true);

			if (Parts::isLegacy($this->parts))
				$this->parts = new PartsLegacy($this->parts);
			else
				$this->parts = new Parts($this->parts);
		}
	}

	// Methods
	// =========================================================================

	public function distance ($to, $unit = 'km')
	{
		// Normalize unit
		if ($unit === 'miles') $unit = 'mi';
		else if ($unit === 'kilometers') $unit = 'km';
		else if (!in_array($unit, ['mi', 'km'])) $unit = 'km';

		// Base Distance
		$distance = $unit === 'km' ? '111.045' : '69.0';

		// Coordinates
		if (
			!is_array($to) ||
			count($to) !== 2 ||
			!array_key_exists('lat', $to) ||
			!array_key_exists('lng', $to)
		) throw new InvalidConfigException('Invalid target lat/lng');

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
