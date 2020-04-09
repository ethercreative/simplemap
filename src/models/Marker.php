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
 * Class Marker
 *
 * @author  Ether Creative
 * @package ether\simplemap\models
 */
class Marker
{

	// Properties
	// =========================================================================

	/** @var string|array Can be an address string, or a [lat, lng] or ['lat' => lat, 'lng' => lng] array */
	public $location;

	/** @var string The colour of the marker in Hex format */
	public $color = '#ff0000';

	/** @var string|null A single character label, or null for no label */
	public $label = null;

	// Constructor
	// =========================================================================

	public function __construct ($config = [])
	{
		if (!empty($config))
			Yii::configure($this, $config);

		if (empty($this->location))
			throw new InvalidConfigException('Marker location is missing!');

		if (empty($this->color))
			throw new InvalidConfigException('Marker colour is missing!');

		$this->color = strtolower($this->color);
		if (preg_match('/^#[a-z0-9]{3}$/', $this->color))
			$this->color = Marker::_expandHex($this->color);

		if ($this->label === '')
			$this->label = null;

		if ($this->label !== null && strlen($this->label) > 1)
			$this->label = $this->label[0];
	}

	// Methods
	// =========================================================================

	public function __toString ()
	{
		return implode('|', [
			Json::encode($this->location),
			$this->color,
			$this->label,
		]);
	}

	public function getLocation ($toLatLng = false)
	{
		if (is_string($this->location))
			return $toLatLng
				? implode(',', array_values(GeoService::latLngFromAddress($this->location)))
				: $this->location;

		return implode(',', array_values($this->location));
	}

	/**
	 * @return array|string|null
	 * @throws \Exception
	 */
	public function getCenter ()
	{
		if (is_string($this->location))
			return GeoService::latLngFromAddress($this->location);

		if (!array_key_exists('lat', $this->location) || !array_key_exists('lng', $this->location))
			return ['lat' => (float)$this->location[0], 'lng' => (float)$this->location[1]];

		return $this->location;
	}

	// Helpers
	// =========================================================================

	private static function _expandHex ($hex)
	{
		$r = $hex[1];
		$g = $hex[2];
		$b = $hex[3];

		return '#' . $r . $r . $g . $g . $b . $b;
	}

}
