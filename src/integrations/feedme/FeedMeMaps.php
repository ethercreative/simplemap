<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\integrations\feedme;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\DataHelper;
use ether\simplemap\elements\Map;
use ether\simplemap\fields\MapField;
use ether\simplemap\models\Parts;

/**
 * Class FeedMeMaps
 *
 * @author  Ether Creative
 * @package ether\simplemap\integrations\feedme
 */
class FeedMeMaps extends Field implements FieldInterface
{

	// Properties
	// =========================================================================

	public static $name = 'Maps';

	public static $class = MapField::class;

	public static $elementType = Map::class;

	// Methods
	// =========================================================================

	public function getMappingTemplate ()
	{
		return 'simplemap/_feedme-mapping';
	}

	public function parseField ()
	{
		$preppedData = [];

		$fields = Hash::get($this->fieldInfo, 'fields');

		if (!$fields)
			return null;

		foreach ($fields as $subFieldHandle => $subFieldInfo)
			$preppedData[$subFieldHandle] = DataHelper::fetchValue(
				$this->feedData,
				$subFieldInfo
			);

		if (isset($preppedData['parts']))
			$preppedData['parts'] = new Parts($preppedData['parts']);

		if (!$preppedData)
			return null;

		return new Map($preppedData);
	}

}
