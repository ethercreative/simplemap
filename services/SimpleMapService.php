<?php

namespace Craft;

class SimpleMapService extends BaseApplicationComponent {

	public function getField (SimpleMap_MapFieldType $fieldType, $value)
	{
		$owner = $fieldType->element;
		$field = $fieldType->model;

		$record = SimpleMap_MapRecord::model()->findByAttributes(array(
			'ownerId'     => $owner->id,
			'fieldId'     => $field->id,
			'ownerLocale' => $owner->locale
		));

		if (craft()->request->getPost() && $value)
		{
			$model = SimpleMap_MapModel::populateModel($value);
		}
		else if ($record)
		{
			$model = SimpleMap_MapModel::populateModel($record->getAttributes());
		}
		else
		{
			$model = new SimpleMap_MapModel;
		}

		$model->distance = null;

		return $model;
	}

	public function saveField (SimpleMap_MapFieldType $fieldType)
	{
		$owner = $fieldType->element;
		$field = $fieldType->model;
		$content = $fieldType->element->getContent();

		$handle = $field->handle;
		$data = $content->getAttribute($handle);

		if (!$data) return false;

		$data['lat'] = (double)$data['lat'];
		$data['lng'] = (double)$data['lng'];

		SimpleMapPlugin::log(print_r(gettype($data['lat']), true));

		$record = SimpleMap_MapRecord::model()->findByAttributes(array(
			'ownerId'     => $owner->id,
			'fieldId'     => $field->id,
			'ownerLocale' => $owner->locale
		));

		if (!$record) {
			$record = new SimpleMap_MapRecord;
			$record->ownerId     = $owner->id;
			$record->fieldId     = $field->id;
			$record->ownerLocale = $owner->locale;
		}

		$record->setAttributes($data, false);

		$save = $record->save();

		if (!$save) {
			SimpleMapPlugin::log(print_r($record->getErrors(), true));
		}

		return $save;
	}

	public function modifyQuery (DbCommand &$query, $params = array())
	{
		//
	}

}