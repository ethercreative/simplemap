<?php

namespace ether\simplemap\migrations;

use craft\db\Migration;
use ether\simplemap\records\MapRecord;

class Install extends Migration
{

	public function safeUp ()
	{
		// 1. Create new table
		// ---------------------------------------------------------------------

		// Table

		$this->createTable(
			MapRecord::$tableName,
			[
				'id'          => $this->primaryKey(),
				'ownerId'     => $this->integer()->notNull(),
				'ownerSiteId' => $this->integer()->notNull(),
				'fieldId'     => $this->integer()->notNull(),

				'lat'     => $this->decimal(11, 9),
				'lng'     => $this->decimal(12, 9),
				'zoom'    => $this->integer(2),
				'address' => $this->string(255),
				'parts'   => $this->text(),

				'dateCreated' => $this->dateTime()->notNull(),
				'dateUpdated' => $this->dateTime()->notNull(),
				'uid'         => $this->uid()->notNull(),
			]
		);

		// Indexes

		$this->createIndex(
			null,
			MapRecord::$tableName,
			['ownerId', 'ownerSiteId', 'fieldId'],
			true
		);

		$this->createIndex(null, MapRecord::$tableName, ['lat'], false);
		$this->createIndex(null, MapRecord::$tableName, ['lng'], false);

		// Relations

		$this->addForeignKey(
			null,
			MapRecord::$tableName,
			['ownerId'],
			'{{%elements}}',
			['id'],
			'CASCADE',
			null
		);

		$this->addForeignKey(
			null,
			MapRecord::$tableName,
			['ownerSiteId'],
			'{{%sites}}',
			['id'],
			'CASCADE',
			'CASCADE'
		);

		$this->addForeignKey(
			null,
			MapRecord::$tableName,
			['fieldId'],
			'{{%fields}}',
			['id'],
			'CASCADE',
			'CASCADE'
		);

		// 2. Transfer data from old table to new (if old exists)
		// ---------------------------------------------------------------------
		// TODO

		// 3. Remove the old table (if exists)
		// ---------------------------------------------------------------------
		// TODO

		return true;
	}

	public function safeDown ()
	{
		$this->dropTableIfExists(MapRecord::$tableName);

		// TODO: Should we handle moving the data back to the old table, or
		// TODO(cont.): will Craft handle that using a backup?

		return true;
	}

}