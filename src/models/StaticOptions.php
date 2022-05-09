<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use ether\simplemap\services\GeoService;
use Exception;
use Yii;
use yii\base\InvalidConfigException;

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
	public string|array $center = [51.272154, 0.514951];

	/** @var string|array Must be [lat, lng] or ['lat' => lat, 'lng' => lng] array */
	public string|array $centerFallback = [51.272154, 0.514951];

	/** @var int The width of the map */
	public int $width = 640;

	/** @var int The height of the map */
	public int $height = 480;

	/** @var int The maps zoom level */
	public int $zoom = 12;

	/** @var int The scale of the map image (i.e. 2 for @2x retina screens) */
	public int $scale = 1;

	/**
	 * @var Marker[] An array of map markers
	 */
	public array $markers = [];

	// Constructor
	// =========================================================================

	/**
	 * StaticOptions constructor.
	 *
	 * @param array $config
	 *
	 * @throws InvalidConfigException
	 * @throws Exception
	 */
	public function __construct (array $config = [])
	{
		$center = $config['center'] ?? null;

		if ($center instanceof Map)
			$center = ['lat' => $center->lat, 'lng' => $center->lng, 'zoom' => $center->zoom];
		elseif ($center instanceof UserLocation)
			$center = ['lat' => $center->lat, 'lng' => $center->lng];
		elseif (is_string($center))
			$center = GeoService::latLngFromAddress($center);

		if (empty($center))
			$center = $config['centerFallback'] ?? $this->centerFallback;

		$config['center'] = $center;

		$markers = $config['markers'] ?? [];
		unset($config['markers']);

		if (!empty($config))
			Yii::configure($this, $config);

		foreach (['center', 'zoom', 'scale'] as $key)
			if (empty($this->$key))
				throw new InvalidConfigException('Map ' . $key . ' is missing!');

		if (!empty($markers))
		{
			foreach ($markers as $marker)
			{
				if (!array_key_exists('location', $marker) || empty($marker['location']))
					$marker['location'] = $this->center;

				$this->markers[] = new Marker($marker);
			}
		}
	}

	// Getters
	// =========================================================================

	/**
	 * @return array|string|null
	 * @throws Exception
	 */
	public function getCenter (): array|string|null
	{
		if (!array_key_exists('lat', $this->center) || !array_key_exists('lng', $this->center))
			$this->center = ['lat' => $this->center[0], 'lng' => $this->center[1]];

		$this->center['lat'] = floatval($this->center['lat']);
		$this->center['lng'] = floatval($this->center['lng']);

		return $this->center;
	}

	public function getSize (): string
	{
		return ($this->width ?? 0) . 'x' . ($this->height ?? 0);
	}

}
