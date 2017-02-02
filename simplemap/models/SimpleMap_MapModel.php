<?php

namespace Craft;

class SimpleMap_MapModel extends BaseModel {
	
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