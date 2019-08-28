<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use ether\simplemap\services\GeoService;
use Yii;

/**
 * Class StaticOptions
 *
 * @author  Ether Creative
 * @package ether\simplemap\models
 */
class StaticOptions
{

	// Properties
	// =========================================================================

	/** @var string|array Can be an address string, or a [lat, lng] or ['lat' => lat, 'lng' => lng] array */
	public $center = [51.272154, 0.514951];

	/** @var int The width of the map */
	public $width = 640;

	/** @var int The height of the map */
	public $height = 480;

	/** @var int The maps zoom level */
	public $zoom = 12;

	/** @var int The scale of the map image (i.e. 2 for @2x retina screens) */
	public $scale = 1;

	// Constructor
	// =========================================================================

	public function __construct ($config = [])
	{
		if (!empty($config))
			Yii::configure($this, $config);
	}

	// Getters
	// =========================================================================

	/**
	 * @return array|string|null
	 * @throws \Exception
	 */
	public function getCenter ()
	{
		if (is_string($this->center))
			$this->center = GeoService::latLngFromAddress($this->center);

		if (!array_key_exists('lat', $this->center) || !array_key_exists('lng', $this->center))
			$this->center = ['lat' => $this->center[0], 'lng' => $this->center[1]];

		return $this->center;
	}

	public function getSize ()
	{
		return $this->width . 'x' . $this->height;
	}

}
