<?php

namespace Craft;

class m160606_162300_simpleMap_updateFieldStorage extends BaseMigration
{
	public function safeUp()
	{
		if (!craft()->db->tableExists('simplemap_maps'))
			craft()->plugins->getPlugin('simpleMap')->createTables();

		$fields = craft()->fields->getAllFields();

		foreach ($fields as $field)
		{
			if ($field->type === 'SimpleMap_Map')
				$this->_transferData($field, 'content');

			if ($field->type === 'Matrix')
				foreach (craft()->matrix->getBlockTypesByFieldId($field->id) as $blockType)
					foreach ($blockType->getFields() as $blockTypeField)
						if ($blockTypeField->type == 'SimpleMap_Map')
							$this->_transferData($blockTypeField, 'matrixcontent_' . $field->handle, $blockType->handle . '_' . $blockTypeField->handle);
		}

		return true;
	}

	private function _transferData(FieldModel $field, $tableName, $fieldHandle = "")
	{
		if (!$fieldHandle)
			$fieldHandle = $field->handle;

		$tableData = craft()->db->createCommand()
			->select('elementId, locale, field_' . $fieldHandle)
			->from($tableName)
			->where('field_' . $fieldHandle . ' IS NOT NULL')
			->queryAll();

		foreach ($tableData as $row) {
			$record = new SimpleMap_MapRecord;
			$record->ownerId     = $row['elementId'];
			$record->fieldId     = $field->id;
			$record->ownerLocale = $row['locale'];

			$f = json_decode($row['field_' . $fieldHandle], true);

			$record->setAttribute('lat', $f['lat']);
			$record->setAttribute('lng', $f['lng']);
			$record->setAttribute('zoom', $f['zoom']);
			$record->setAttribute('address', $f['address']);
			if ($f !== null && array_key_exists('parts', $f))
				$record->setAttribute('parts', json_encode($f['parts']));

			$record->save();
		}
	}

}