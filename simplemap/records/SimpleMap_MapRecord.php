<?php

namespace Craft;

class SimpleMap_MapRecord extends BaseRecord {

	public static $dec = array(AttributeType::Number, 'column' => ColumnType::Decimal, 'length' => 12, 'decimals' => 8);
	const TABLE_NAME = 'simplemap_maps';

	public function getTableName()
	{
		return static::TABLE_NAME;
	}

	public function defineRelations()
	{
		return array(
			'owner'       => array(static::BELONGS_TO, 'ElementRecord', 'required' => true, 'onDelete' => static::CASCADE),
			'ownerLocale' => array(static::BELONGS_TO, 'LocaleRecord', 'ownerLocale', 'onDelete' => static::CASCADE, 'onUpdate' => static::CASCADE),
			'field'       => array(static::BELONGS_TO, 'FieldRecord', 'required' => true, 'onDelete' => static::CASCADE)
		);
	}

	public function defineIndexes()
	{
		return array(
			array('columns' => array('ownerId')),
			array('columns' => array('fieldId')),
			array('columns' => array('lat')),
			array('columns' => array('lng')),
		);
	}

	protected function defineAttributes()
	{
		return array(
			'ownerLocale'   => AttributeType::Locale,
			'lat'           => SimpleMap_MapRecord::$dec,
			'lng'           => SimpleMap_MapRecord::$dec,
			'zoom'          => AttributeType::Number,
			'address'       => AttributeType::String,
			'parts'         => AttributeType::Mixed
		);
	}

}