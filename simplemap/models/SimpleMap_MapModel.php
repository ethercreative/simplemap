<?php

namespace Craft;

/**
 * Class SimpleMap_MapModel
 *
 * @property int $ownerId
 * @property int $fieldId
 * @property string $ownerLocale
 * @property double $lat
 * @property double $lng
 * @property int $zoom
 * @property string $address
 * @property array $parts
 * @property double $distance
 *
 * @package Craft
 */
class SimpleMap_MapModel extends BaseModel {

	public function __toString ()
	{
		return $this->address ?: "";
	}

	protected function defineAttributes()
	{
		return array(
			'ownerId'       => AttributeType::Number,
			'fieldId'       => AttributeType::Number,
			'ownerLocale'   => AttributeType::Locale,
			'lat'           => SimpleMap_MapRecord::$dec,
			'lng'           => SimpleMap_MapRecord::$dec,
			'zoom'          => AttributeType::Number,
			'address'       => AttributeType::String,
			'parts'         => AttributeType::Mixed,

			'distance'      => SimpleMap_MapRecord::$dec,
		);
	}

}