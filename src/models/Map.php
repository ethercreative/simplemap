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
use Exception;
use Twig\Markup;
use yii\base\InvalidConfigException;

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
	public int $id;

	/** @var int */
	public int $ownerId;

	/** @var int */
	public int $ownerSiteId;

	/** @var int */
	public int $fieldId;

	/** @var int */
	public int $zoom = 15;

	/** @var float|null */
	public int|null|float $distance = null;

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

	public function canGetProperty ($name, $checkVars = true, $checkBehaviors = true): bool
	{
		try
		{
			if (
				property_exists($this->parts, $name) ||
				$name === 'streetAddress' ||
				in_array($name, PartsLegacy::$legacyKeys)
			) return true;
		} catch (Exception $e) {
			return false;
		}

		return parent::canGetProperty($name, $checkVars, $checkBehaviors);
	}

	// Methods
	// =========================================================================

	public function rules (): array
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

	public function isValueEmpty (): bool
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
	 * @throws Exception
	 */
	public function img (array $options = [])
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
	 * @throws Exception
	 */
	public function imgSrcSet (array $options = []): string
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
	 * @throws InvalidConfigException
	 */
	public function embed (array $options = [])
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
	private function _getMapOptions (array $options): array
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
