<?php

namespace ether\simplemap\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use ether\simplemap\records\Map;
use ether\simplemap\elements\Map as MapElement;

/**
 * m190325_130533_repair_map_elements migration.
 */
class m190325_130533_repair_map_elements extends Migration
{
	/**
	 * @inheritdoc
	 * @return bool
	 * @throws \yii\base\Exception
	 * @throws \yii\base\NotSupportedException
	 */
    public function safeUp()
    {
        if (!$this->db->columnExists(Map::TableName, 'elementId'))
        	return true;

	    echo '    > Start map data fix' . PHP_EOL;

        $rows = (new Query())
	        ->select('*')
	        ->from(Map::TableName)
	        ->orderBy('dateUpdated DESC')
	        ->all();

        $validMapElementIds = (new Query())
	        ->select('id')
	        ->from(Table::ELEMENTS)
	        ->where(['=', 'type', MapElement::class])
	        ->column();

        $this->dropTable(Map::TableName);
	    (new Install())->safeUp();

	    $updatedElementIds = [];

        foreach ($rows as $row)
        {
        	// Skip any rows that don't have a matching element
        	if (!in_array($row['elementId'], $validMapElementIds))
        		continue;

        	// Skip and duplicate elements
	        if (in_array($row['elementId'], $updatedElementIds))
	        	continue;

	        echo '    > Fix map value ' . $row['address'] . PHP_EOL;

	        $record              = new Map();
	        $record->id          = $row['elementId'];
	        $record->ownerId     = $row['ownerId'];
	        $record->ownerSiteId = $row['ownerSiteId'];
	        $record->fieldId     = $row['fieldId'];

	        $record->lat     = $row['lat'];
	        $record->lng     = $row['lng'];
	        $record->zoom    = $row['zoom'];
	        $record->address = $row['address'];
	        $record->parts   = $row['parts'];

	        $record->save(false);

	        $updatedElementIds[] = $record->id;
        }

	    return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190325_130533_repair_map_elements cannot be reverted.\n";
        return false;
    }
}
