<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\maps\elements;

use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Json;
use craft\records\Site;
use ether\maps\elements\db\MapQuery;
use ether\maps\models\Parts;
use ether\maps\models\PartsLegacy;
use ether\maps\Maps;

/**
 * Class Map
 *
 * @author  Ether Creative
 * @package ether\maps\elements
 */
class Map extends Element
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
	public $zoom;

	/** @var string */
	public $address;

	/** @var Parts */
	public $parts;

	/** @var float|null */
	public $distance = null;

	// Methods
	// =========================================================================

	public function __construct (array $config = [])
	{
		parent::__construct($config);

		if ($this->address === null)
			$this->address = '';

		if ($this->parts && !is_array($this->parts))
			$this->parts = Json::decodeIfJson($this->parts);

		if (Parts::isLegacy($this->parts))
			$this->parts = new PartsLegacy($this->parts);
		else
			$this->parts = new Parts($this->parts);

		$this->distance = Maps::getInstance()->map->getDistance($this);
	}

	// Methods: Static
	// -------------------------------------------------------------------------

	public static function displayName (): string
	{
		return Maps::t('Map');
	}

	public static function refHandle ()
	{
		return 'map';
	}

	public static function hasContent (): bool
	{
		return false;
	}

	public static function isLocalized (): bool
	{
		return true;
	}

	public static function hasStatuses (): bool
	{
		return false;
	}

	public function getSupportedSites (): array
	{
		return Site::find()->select('id')->column();
	}

	/**
	 * @return ElementQueryInterface|MapQuery
	 */
	public static function find (): ElementQueryInterface
	{
		return new MapQuery(static::class);
	}

	public function isValueEmpty (): bool
	{
		return empty($this->lat) && empty($this->lng);
	}

	// Methods: Instance
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 */
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

	/**
	 * @param bool $isNew
	 *
	 * @throws \yii\db\Exception
	 */
	public function afterSave (bool $isNew)
	{
		Maps::getInstance()->map->saveRecord(
			$this,
			$this->ownerId,
			$this->ownerSiteId,
			$this->fieldId,
			$isNew
		);

		parent::afterSave($isNew);
	}

}
