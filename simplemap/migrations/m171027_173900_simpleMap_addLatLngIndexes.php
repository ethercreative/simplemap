<?php

namespace Craft;

class m171027_173900_simpleMap_addLatLngIndexes extends BaseMigration {

	public function safeUp ()
	{
		$this->createIndex(
			SimpleMap_MapRecord::TABLE_NAME,
			'lat'
		);

		$this->createIndex(
			SimpleMap_MapRecord::TABLE_NAME,
			'lng'
		);

		return true;
	}

}