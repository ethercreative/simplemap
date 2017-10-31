<?php

namespace ether\SimpleMap\migrations;

use craft\db\Migration;

class Install extends Migration
{

	public function safeUp ()
	{
		$this->createTable(
			'{{%simplemaps}}',
			[
				'id' => $this->primaryKey(),
			]
		);

		echo " done\n";
	}

	public function safeDown ()
	{
		$this->dropTableIfExists('{{%simplemaps}}');

		return true;
	}

}