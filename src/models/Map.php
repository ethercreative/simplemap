<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use craft\base\Model;
use craft\helpers\Json;
use ether\simplemap\SimpleMap;
use Twig\Markup;

/**
 * Class Map
 *
 * @author  Ether Creative
 * @package ether\simplemap\models
 */
class Map extends BaseLocation
{

	// Properties
	// =========================================================================

	/** @var int */
	public $id;

	/** @var int */
	public $ownerId;

	/** @var int */
	public $ownerSiteId;

	/** @var int */
	public $fieldId;

	/** @var int */
	public $zoom = 15;

	/** @var float|null */
	public $distance = null;

	// Constructor
	// =========================================================================

	public function __construct ($config = [])
	{
		foreach (['id', 'ownerId', 'ownerSiteId', 'fieldId'] as $key)
			if (array_key_exists($key, $config))
				unset($config[$key]);

		parent::__construct($config);

		$this->distance = SimpleMap::getInstance()->map->getDistance($this);
	}

	// Getters
	// =========================================================================

	public function __get ($name)
	{
		$isPart = property_exists($this->parts, $name) || $name === 'streetAddress';

		if (in_array($name, PartsLegacy::$legacyKeys) && !$isPart)
			return null;
		else if ($isPart)
			return $this->parts->$name;

		return parent::__get($name);
	}

	public function canGetProperty ($name, $checkVars = true, $checkBehaviors = true)
	{
		try
		{
			if (
				property_exists($this->parts, $name) ||
				$name === 'streetAddress' ||
				in_array($name, PartsLegacy::$legacyKeys)
			) return true;
		} catch (\Exception $e) {
			return false;
		}

		return parent::canGetProperty($name, $checkVars, $checkBehaviors);
	}

	// Methods
	// =========================================================================

	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['zoom'],
			'required',
		];
		$rules[] = [
			['lat'],
			'double',
			'min' => -90,
			'max' => 90,
		];
		$rules[] = [
			['lng'],
			'double',
			'min' => -180,
			'max' => 180,
		];

		return $rules;
	}

	public function isValueEmpty ()
	{
		return empty($this->lat) && empty($this->lng);
	}

	// Render Map
	// =========================================================================

	// Render Map: Image
	// -------------------------------------------------------------------------

	/**
	 * Output the map field as a static image
	 *
	 * @param array $options
	 *
	 * @return string|void
	 * @throws \Exception
	 */
	public function img ($options = [])
	{
		return SimpleMap::getInstance()->static->generate(
			$this->_getMapOptions($options)
		);
	}

	/**
	 * Output the map ready for srcset
	 *
	 * @param array $options
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function imgSrcSet ($options = [])
	{
		$options = $this->_getMapOptions($options);

		$x1 = $this->img(array_merge($options, ['scale' => 1]));
		$x2 = $this->img(array_merge($options, ['scale' => 2]));

		return $x1 . ' 1x, ' . $x2 . ' 2x';
	}

	/**
	 * Output an interactive map
	 *
	 * @param array $options
	 *
	 * @return string|void
	 * @throws \yii\base\InvalidConfigException
	 */
	public function embed ($options = [])
	{
		$options = $this->_getMapOptions($options);
		return SimpleMap::getInstance()->embed->embed($options);
	}

	// Helpers
	// =========================================================================

	/**
	 * Merge options w/ map properties
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	private function _getMapOptions ($options)
	{
		return array_merge($options, [
			'center' => [
				$this->lat,
				$this->lng,
			],
			'zoom'   => $this->zoom,
		]);
	}

}
