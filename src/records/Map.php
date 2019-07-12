<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use craft\records\Field;
use craft\records\Site;
use yii\db\ActiveQueryInterface;

/**
 * Class Map
 *
 * @property int    $id
 * @property int    $ownerId
 * @property int    $ownerSiteId
 * @property int    $fieldId
 * @property float  $lat
 * @property float  $lng
 *
 * @author  Ether Creative
 * @package ether\simplemap\records
 */
class Map extends ActiveRecord
{

	// Properties
	// =========================================================================

	/** @deprecated Remove in 4.x */
	const OldTableName = '{{%simplemaps}}';

	const TableName = '{{%maps}}';
	const TableNameClean = 'maps';
	
	// Methods
	// =========================================================================

	public static function tableName ()
	{
		return self::TableName;
	}

	public function getOwner (): ActiveQueryInterface
	{
		return $this->hasOne(Element::class, ['id' => 'ownerId']);
	}

	public function getOwnerSite (): ActiveQueryInterface
	{
		return $this->hasOne(Site::class, ['id' => 'ownerSiteId']);
	}

	public function getField (): ActiveQueryInterface
	{
		return $this->hasOne(Field::class, ['id' => 'fieldId']);
	}

}
