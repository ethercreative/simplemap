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

/**
 * Class Map
 *
 * @author  Ether Creative
 * @package ether\simplemap\models
 */
class Map extends Model
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

	/** @var float */
	public $lat;

	/** @var float */
	public $lng;

	/** @var int */
	public $zoom = 15;

	/** @var string */
	public $address;

	/** @var Parts */
	public $parts;

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

		if ($this->address === null)
			$this->address = '';

		if (!($this->parts instanceof Parts))
		{
			if ($this->parts && !is_array($this->parts))
				$this->parts = Json::decodeIfJson($this->parts, true);

			if (Parts::isLegacy($this->parts))
				$this->parts = new PartsLegacy($this->parts);
			else
				$this->parts = new Parts($this->parts);
		}

		$this->distance = SimpleMap::getInstance()->map->getDistance($this);
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

}
