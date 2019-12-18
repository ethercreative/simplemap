<?php

namespace ether\simplemap\migrations;

use Craft;
use craft\base\FieldInterface;
use craft\db\Migration;
use craft\db\Query;
use craft\fields\Matrix;
use craft\helpers\Json;
use ether\simplemap\fields\MapField;
use ether\simplemap\models\Map;
use ether\simplemap\records\Map as MapRecord;
use ether\simplemap\SimpleMap;
use verbb\supertable\fields\SuperTableField;

/**
 * m190712_104805_new_data_format migration.
 */
class m190712_104805_new_data_format extends Migration
{

	/**
	 * @inheritdoc
	 * @throws \yii\db\Exception
	 * @throws \Exception
	 */
    public function safeUp ()
    {
    	$db = $this->getDb();
    	$mapService = SimpleMap::getInstance()->map;
    	$matrixService = Craft::$app->getMatrix();
    	$superTableService = null;
    	$hasSuperTable = class_exists(SuperTableField::class);

        // 1. Add content columns
        // ---------------------------------------------------------------------

	    echo '1. Creating Maps content columns' . PHP_EOL;

	    $matrixFields = [];
	    $superTableFields = [];

	    $fields = array_reduce(
		    Craft::$app->getFields()->getAllFields(),
		    function ($carry, FieldInterface $field) use ($hasSuperTable, &$matrixFields, &$superTableFields) {
		    	if ($field instanceof MapField)
			    	$carry[$field->id] = $field;

		    	elseif ($field instanceof Matrix)
				    $matrixFields[] = $field;

		    	elseif ($hasSuperTable && $field instanceof SuperTableField)
				    $superTableFields[] = $field;

		    	return $carry;
		    },
		    []
	    );

	    $matrixMapFields = [];
	    $superTableMapFields = [];

	    $matrixMapFields = array_merge(
	    	$matrixMapFields,
		    $this->_reduceMatrixFields(
			    $matrixFields,
			    $hasSuperTable,
			    $matrixMapFields,
			    $superTableMapFields
		    )
	    );

	    $superTableMapFields = array_merge(
	    	$superTableMapFields,
		    $this->_reduceSuperTableFields(
			    $superTableFields,
			    $matrixMapFields,
			    $superTableMapFields
		    )
	    );

	    $fieldIdToMatrixBlockHandle = [];

	    if (!empty($matrixMapFields))
	    {
	    	foreach ($matrixFields as $field)
		    {
		    	$blockTypes = $matrixService->getBlockTypesByFieldId($field->id);

		    	foreach ($blockTypes as $blockType)
			    	foreach ($blockType->getFields() as $field)
				    	$fieldIdToMatrixBlockHandle[$field->id] = $blockType->handle;
		    }
	    }

	    $columnType = (new MapField())->getContentColumnType();
	    $contentTable = Craft::$app->getContent()->contentTable;
	    $fieldColumnPrefix = Craft::$app->getContent()->fieldColumnPrefix;

	    /** @var MapField $field */
	    foreach ($fields as $field)
	    {
	    	echo '- Create content column for ' . $field->name . ' in content table' . PHP_EOL;

	    	$exists = $this->db->columnExists(
			    $contentTable,
			    $fieldColumnPrefix . $field->handle
		    );

	    	if ($exists)
		    {
			    $this->alterColumn(
				    $contentTable,
				    $fieldColumnPrefix . $field->handle,
				    $columnType
			    );
			    continue;
		    }

		    $this->addColumn(
			    $contentTable,
			    $fieldColumnPrefix . $field->handle,
			    $columnType
		    );
	    }

	    foreach ($matrixMapFields as $table => $mmFields)
	    {
	    	foreach ($mmFields as $field)
		    {
		    	if (!$blockTypeHandle = @$fieldIdToMatrixBlockHandle[$field->id])
		    		continue;

			    echo '- Create content column for ' . $field->name . ' in matrix ' . $blockTypeHandle . PHP_EOL;

			    $handle =
				    $fieldColumnPrefix . $blockTypeHandle . '_' . $field->handle;

			    $exists = $this->db->columnExists(
				    $table,
				    $handle
			    );

			    if ($exists)
			    {
			    	$this->alterColumn(
			    		$table,
					    $handle,
					    $columnType
				    );
				    continue;
			    }

			    $this->addColumn(
				    $table,
				    $handle,
				    $columnType
			    );
		    }
	    }

	    foreach ($superTableMapFields as $table => $stFields)
	    {
	    	foreach ($stFields as $field)
		    {
			    echo '- Create content column for ' . $field->name . ' in super table' . PHP_EOL;

			    $exists = $this->db->columnExists(
				    $table,
				    $fieldColumnPrefix . $field->handle
			    );

			    if ($exists)
			    {
				    $this->alterColumn(
					    $table,
					    $fieldColumnPrefix . $field->handle,
					    $columnType
				    );
				    continue;
			    }

			    $this->addColumn(
				    $table,
				    $fieldColumnPrefix . $field->handle,
				    $columnType
			    );
		    }
	    }

	    // 2. Create new maps table
	    // ---------------------------------------------------------------------

	    echo '2. Creating new Maps table' . PHP_EOL;

	    if ($this->db->tableExists(MapRecord::TableName))
	    {
	    	$rawTableName = $this->getDb()->getSchema()->getRawTableName(
			    MapRecord::TableName
		    );

	    	if ($this->getDb()->getDriverName() === 'pgsql')
		    {
		    	$indexNames = $this->getDb()->createCommand(
				    'SELECT indexname FROM pg_indexes WHERE [[tablename]]=:tablename',
				    ['tablename' => $rawTableName]
			    )->queryColumn();

		    	foreach ($indexNames as $name)
		    		$this->getDb()->createCommand(
		    			'ALTER INDEX "' . $name . '" RENAME TO "' . $name . '_old"'
				    )->execute();
		    }
	    	else
		    {
			    $indexNames = $this->getDb()->createCommand(
				    'SHOW INDEX FROM ' . $rawTableName
			    )->queryAll();

			    $indexNames = array_unique(array_reduce(
			    	$indexNames,
				    function ($carry, $row) {
			    		if ($row['Key_name'] === 'PRIMARY')
			    			return $carry;

			    		$carry[] = $row['Key_name'];
			    		return $carry;
				    },
				    []
			    ));

			    // Look, I know this is hacky but whatever who still uses MySQL
			    // anyway? You know Postgres exists, right?
			    $this->getDb()->createCommand('SET foreign_key_checks = 0;')->execute();

			    foreach ($indexNames as $name)
				    $this->dropIndex($name, MapRecord::TableName);

			    $this->getDb()->createCommand('SET foreign_key_checks = 1;')->execute();
		    }

		    $this->renameTable(MapRecord::TableName, MapRecord::OldTableName);
	    }

        (new Install())->safeUp();

	    // 3. Move content to table
	    // ---------------------------------------------------------------------

	    echo '3. Moving existing maps content' . PHP_EOL;

	    $contentRows = (new Query())
		    ->select('id, elementId, siteId')
		    ->from($contentTable);

	    foreach ($contentRows->each() as $row)
	    {
	    	$mapContent = $this->_getMapContent(
	    		$row['elementId'],
			    $row['siteId']
		    );

	    	if (empty($mapContent))
	    		continue;

	    	foreach ($mapContent as $mapData)
		    {
		    	$map = new Map($mapData);

		    	echo '- Moving ' . $map->address . ' (' . $mapData['id'] . ') to ' . $contentTable . PHP_EOL;

		    	$map->ownerId = $row['elementId'];
		    	$map->ownerSiteId = $row['siteId'];
		    	$map->fieldId = $mapData['fieldId'];

		    	$field = @$fields[$mapData['fieldId']];

		    	// Skip if the field no longer exists
		    	if (!$field)
			    {
				    echo '- Skipping ' . $map->address . ' (' . $mapData['id'] . ') - Field no longer exists' . PHP_EOL;
				    continue;
			    }

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

	    foreach ($matrixMapFields as $contentTable => $fields)
	    {
	    	$contentRows = (new Query())
			    ->select('id, elementId, siteId')
			    ->from($contentTable);

	    	foreach ($contentRows->each() as $row)
		    {
		    	$mapContent = $this->_getMapContent(
		    		$row['elementId'],
				    $row['siteId']
			    );

		    	if (empty($mapContent))
		    		continue;

		    	foreach ($mapContent as $mapData)
			    {
				    if (!$blockHandle = @$fieldIdToMatrixBlockHandle[$mapData['fieldId']])
					    continue;

				    $map = new Map($mapData);

				    $map->ownerId     = $row['elementId'];
				    $map->ownerSiteId = $row['siteId'];
				    $map->fieldId     = $mapData['fieldId'];

				    $field = $fields[$mapData['fieldId']];
				    $col   = $fieldColumnPrefix . $blockHandle . '_' . $field->handle;

				    echo '- Moving ' . $map->address . ' (' . $mapData['id'] . ') to ' . $contentTable . ', ' . $col . PHP_EOL;

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
	    }

	    foreach ($superTableMapFields as $contentTable => $fields)
	    {
	    	$contentRows = (new Query())
			    ->select('id, elementId, siteId')
			    ->from($contentTable);

	    	foreach ($contentRows->each() as $row)
		    {
		    	$mapContent = $this->_getMapContent(
		    		$row['elementId'],
				    $row['siteId']
			    );

		    	if (empty($mapContent))
		    		continue;

		    	foreach ($mapContent as $mapData)
			    {
				    $map = new Map($mapData);

				    $map->ownerId     = $row['elementId'];
				    $map->ownerSiteId = $row['siteId'];
				    $map->fieldId     = $mapData['fieldId'];

				    $field = $fields[$mapData['fieldId']];
				    $col   = $fieldColumnPrefix . $field->handle;

				    echo '- Moving ' . $map->address . ' (' . $mapData['id'] . ') to ' . $contentTable . ', ' . $col . PHP_EOL;

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
	    }

	    // 4. Drop old data table
	    // ---------------------------------------------------------------------

	    $this->dropTableIfExists(MapRecord::OldTableName);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190712_104805_new_data_format cannot be reverted.\n";
        return false;
    }

    // Helpers
    // =========================================================================

	private function _reduceMatrixFields ($matrixFields, $hasSuperTable, &$matrixMapFields, &$superTableMapFields)
	{
		return array_reduce(
			$matrixFields,
			function ($carry, Matrix $matrix) use (
				$hasSuperTable, &$matrixMapFields, &$superTableMapFields
			) {
				$fields = [];

				foreach ($matrix->getBlockTypeFields() as $field)
				{
					if ($field instanceof MapField)
						$fields[$field->id] = $field;

					elseif ($hasSuperTable && $field instanceof SuperTableField)
						$superTableMapFields = array_merge(
							$superTableMapFields,
							$this->_reduceSuperTableFields(
								[$field],
								$matrixMapFields,
								$superTableMapFields
							)
						);
				}

				if (!empty($fields))
					$carry[$matrix->contentTable] = $fields;

				return $carry;
			},
			[]
		);
	}

	private function _reduceSuperTableFields ($superTableFields, &$matrixMapFields, &$superTableMapFields)
	{
		return array_reduce(
			$superTableFields,
			function ($carry, SuperTableField $superTable) use (
				&$matrixMapFields, &$superTableMapFields
			) {
				$fields = [];

				foreach ($superTable->getBlockTypeFields() as $field)
				{
					if ($field instanceof MapField)
						$fields[$field->id] = $field;

					elseif ($field instanceof Matrix)
						$matrixMapFields = array_merge(
							$matrixMapFields,
							$this->_reduceMatrixFields(
								[$field],
								true,
								$matrixMapFields,
								$superTableMapFields
							)
						);
				}

				if (!empty($fields))
					$carry[$superTable->contentTable] = $fields;

				return $carry;
			},
			[]
		);
	}

	private function _getMapContent ($elementId, $siteId)
	{
		return (new Query())
			->select(
				'id, ownerId, ownerSiteId, fieldId, lat, lng, zoom, address, parts'
			)
			->from(MapRecord::OldTableName)
			->where([
				'ownerId' => $elementId,
				'ownerSiteId' => $siteId,
			])
			->groupBy('id, fieldId')
			->orderBy('dateUpdated')
			->all();
	}

}
