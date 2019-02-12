<?php

namespace ether\simplemap\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Table;
use ether\simplemap\records\Map;

/**
 * m190212_075031_add_multisite_db_support migration.
 */
class m190212_075031_update_maps_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
    	$this->addColumn(
    		Map::TableName,
		    'elementId',
		    'integer not null'
	    );

    	$this->alterColumn(
    		Map::TableName,
		    'ownerSiteId',
		    'integer'
	    );

    	$this->alterColumn(
    		Map::TableName,
		    'ownerSiteId',
		    'integer'
	    );

	    $this->addForeignKey(
		    null,
		    Map::TableName,
		    ['elementId'],
		    Table::ELEMENTS,
		    ['id'],
		    'CASCADE'
	    );

	    $this->addForeignKey(
		    null,
		    Map::TableName,
		    ['ownerSiteId'],
		    Table::SITES,
		    ['id'],
		    'CASCADE',
		    'CASCADE'
	    );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190212_075031_add_multisite_db_support cannot be reverted.\n";
        return false;
    }
}
