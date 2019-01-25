<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\migrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;
use ether\simplemap\records\Map;

/**
 * Class m000000_000000_update_map_table_structure
 *
 * @author  Ether Creative
 * @package ether\simplemap\migrations
 */
class m000000_000000_update_map_table_structure extends Migration
{

	/**
	 * @return bool|void
	 * @throws \yii\base\NotSupportedException
	 */
	public function safeUp ()
	{
		if (!$this->db->columnExists(Map::TableName, 'siteId'))
		{
			MigrationHelper::renameColumn(
				Map::TableName,
				'ownerSiteId',
				'siteId',
				$this
			);
		}
	}

	public function safeDown ()
	{
		echo "m000000_000000_update_map_table_structure cannot be reverted.\n";

		return false;
	}

}