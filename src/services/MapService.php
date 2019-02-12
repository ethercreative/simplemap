<?php
/**
 * Maps for Craft CMS 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\services;

use craft\base\Component;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\helpers\Json;
use ether\simplemap\fields\Map;
use ether\simplemap\elements\Map as MapElement;
use ether\simplemap\records\Map as MapRecord;

/**
 * Class MapService
 *
 * @author  Ether Creative
 * @package ether\simplemap\services
 */
class MapService extends Component
{

	/**
	 * @param Map              $field
	 * @param ElementInterface|Element $element
	 *
	 * @throws \Throwable
	 * @throws \yii\db\Exception
	 */
	public function saveField (Map $field, ElementInterface $element)
	{
		$craft = \Craft::$app;

		$transaction = $craft->getDb()->beginTransaction();

		try
		{
			/** @var MapElement $value */
			$value = $element->getFieldValue($field->handle);
			$craft->elements->saveElement($value);

			$record = null;

			if ($value->id)
			{
				$record = MapRecord::findOne([
					'elementId' => $value->id,
					'ownerSiteId' => $element->site->id,
				]);
			}

			if ($record === null)
				$record = new MapRecord();

			$record->elementId = $value->id;
			$record->ownerId = $element->id;
			$record->ownerSiteId = $element->site->id;
			$record->fieldId = $field->id;

			$record->lat = $value->lat;
			$record->lng = $value->lng;
			$record->zoom = $value->zoom;
			$record->address = $value->address;
			$record->parts = $value->parts;

			$record->save();
		}
		catch (\Throwable $e)
		{
			$transaction->rollBack();

			throw $e;
		}

		$transaction->commit();
	}

}