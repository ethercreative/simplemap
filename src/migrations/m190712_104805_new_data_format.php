<?php

namespace ether\simplemap\migrations;

use Craft;
use craft\base\FieldInterface;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use ether\simplemap\fields\MapField;
use ether\simplemap\models\Map;
use ether\simplemap\records\Map as MapRecord;
use ether\simplemap\SimpleMap;

/**
 * m190712_104805_new_data_format migration.
 */
class m190712_104805_new_data_format extends Migration
{

	/**
	 * @inheritdoc
	 * @throws \yii\db\Exception
	 */
    public function safeUp ()
    {
    	$db = $this->getDb();
    	$mapService = SimpleMap::getInstance()->map;

        // 1. Add content columns
        // ---------------------------------------------------------------------

	    echo '1. Creating Maps content columns' . PHP_EOL;

	    $fields = array_reduce(
		    Craft::$app->getFields()->getAllFields(),
		    function ($carry, FieldInterface $field) {
		    	if ($field instanceof MapField)
			    	$carry[$field->id] = $field;

		    	return $carry;
		    },
		    []
	    );

	    $columnType = (new MapField())->getContentColumnType();
	    $contentTable = Craft::$app->getContent()->contentTable;
	    $fieldColumnPrefix = Craft::$app->getContent()->fieldColumnPrefix;

	    /** @var MapField $field */
	    foreach ($fields as $field)
	    {
	    	echo '- Create content column for ' . $field->name . PHP_EOL;

	    	$this->addColumn(
			    $contentTable,
			    $fieldColumnPrefix . $field->handle,
			    $columnType
		    );
	    }

	    // 2. Create new maps table
	    // ---------------------------------------------------------------------

	    echo '2. Creating new Maps table' . PHP_EOL;

	    (new Install())->safeUp();

	    // 3. Move content to table
	    // ---------------------------------------------------------------------

	    echo '3. Moving existing maps content' . PHP_EOL;

	    $contentRows = (new Query())
		    ->select('id, elementId, siteId')
		    ->from($contentTable);

	    foreach ($contentRows->each() as $row)
	    {
	    	$mapContent = (new Query())
			    ->select('id, ownerId, ownerSiteId, fieldId, lat, lng, zoom, address, parts')
			    ->from(MapRecord::OldTableName)
			    ->where([
			    	'ownerId' => $row['elementId'],
			    	'ownerSiteId' => $row['siteId'],
			    ])
			    ->groupBy('id, fieldId')
			    ->orderBy('dateUpdated')
			    ->all();

	    	if (empty($mapContent))
	    		continue;

	    	foreach ($mapContent as $mapData)
		    {
		    	$map = new Map($mapData);

		    	echo '- Moving ' . $map->address . ' (' . $map->id . ')' . PHP_EOL;

		    	$map->ownerId = $row['elementId'];
		    	$map->ownerSiteId = $row['siteId'];
		    	$map->fieldId = $mapData['fieldId'];

		    	$field = $fields[$mapData['fieldId']];
		    	$col = $fieldColumnPrefix . $field->handle;

		    	$db->createCommand()
				    ->update(
				    	$contentTable,
					    [$col => Json::encode($map)],
					    ['id' => $row['id']]
				    )
				    ->execute();

		    	$mapService->saveRecord($map, true);
		    }
	    }

	    // 4. Drop old data table
	    // ---------------------------------------------------------------------

	    $this->dropTable(MapRecord::OldTableName);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190712_104805_new_data_format cannot be reverted.\n";
        return false;
    }

}
