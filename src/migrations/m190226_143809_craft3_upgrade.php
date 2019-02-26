<?php

namespace ether\simplemap\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use craft\helpers\Json;
use craft\validators\HandleValidator;
use ether\simplemap\records\Map;
use ether\simplemap\elements\Map as MapElement;
use ether\simplemap\fields\Map as MapField;

/**
 * m190226_143809_craft3_upgrade migration.
 */
class m190226_143809_craft3_upgrade extends Migration
{
	// Properties
	// =========================================================================

	static $sitesByOldLocale = [];

	// Methods
	// =========================================================================

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        // 1. Run the install migration
	    if (!$this->db->tableExists(Map::TableName))
	        (new Install())->safeUp();

	    // 2. Upgrade the data
	    if ($this->db->tableExists('{{%simplemap_maps}}'))
	    	$this->_upgrade2();
	    else
	    	$this->_upgrade3();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190226_143809_craft3_upgrade cannot be reverted.\n";
        return false;
    }

	/**
	 * Upgrade from Craft 2
	 *
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \yii\base\Exception
	 * @throws \yii\db\Exception
	 */
    private function _upgrade2 ()
    {
    	$craft = \Craft::$app;
    	$elements = $craft->elements;

	    // 1. Update the old data
	    $rows = (new Query())
		    ->select('*')
		    ->from('{{%simplemap_maps}}')
		    ->all();

	    foreach ($rows as $row)
	    {
	    	$site = $this->getSiteByLocale($row['ownerLocale']);

	    	$map = new MapElement([
			    'ownerId'     => $row['ownerId'],
			    'ownerSiteId' => $site->id,
			    'fieldId'     => $row['fieldId'],
			    'lat'         => $row['lat'],
			    'lng'         => $row['lng'],
			    'zoom'        => $row['zoom'],
			    'address'     => $row['address'],
			    'parts'       => $row['parts'],
		    ]);

	    	$elements->saveElement($map, false);

		    $record              = new Map();
		    $record->elementId   = $map->id;
		    $record->ownerId     = $map->ownerId;
		    $record->ownerSiteId = $map->ownerSiteId;
		    $record->fieldId     = $map->fieldId;

		    $record->lat     = $map->lat;
		    $record->lng     = $map->lng;
		    $record->zoom    = $map->zoom;
		    $record->address = $map->address;
		    $record->parts   = $map->parts;

		    $record->save();
	    }

	    // 2. Update old field types
	    $rows = (new Query())
		    ->select(['id', 'settings'])
		    ->from(Table::FIELDS)
		    ->where(['type' => 'SimpleMap_Map'])
		    ->all();

	    foreach ($rows as $row)
	    {
	    	$id = $row['id'];
	    	$oldSettings = Json::decodeIfJson($row['settings']);

	    	$newSettings = [
			    'lat'     => $oldSettings['lat'],
			    'lng'     => $oldSettings['lng'],
			    'zoom'    => $oldSettings['zoom'],
			    'country' => strtoupper($oldSettings['countryRestriction']),
			    'hideMap' => $oldSettings['hideMap'],
		    ];

		    $this->db->createCommand()
			    ->update(
			    	Table::FIELDS,
				    [
				    	'type' => MapField::class,
					    'settings' => Json::encode($newSettings),
				    ],
				    compact('id')
			    )
			    ->execute();
	    }
    }

	/**
	 * Upgrade from SimpleMap (3.3.x)
	 *
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \yii\base\Exception
	 */
    private function _upgrade3 ()
    {
	    $craft    = \Craft::$app;
	    $elements = $craft->elements;

	    // 1. Store the old data
	    $rows = (new Query())
		    ->select([
		    	'ownerId',
			    'ownerSiteId',
			    'fieldId',
			    'lat',
			    'lng',
			    'zoom',
			    'address',
			    'parts',
		    ])
		    ->from(Map::TableName)
		    ->all();

	    // 2. Re-create the table
	    $this->dropTable(Map::TableName);
	    (new Install())->safeUp();

	    // 3. Store the old data as new
	    foreach ($rows as $row)
	    {
		    $map = new MapElement($row);

		    $elements->saveElement($map);

		    $record              = new Map();
		    $record->elementId   = $map->id;
		    $record->ownerId     = $map->ownerId;
		    $record->ownerSiteId = $map->ownerSiteId;
		    $record->fieldId     = $map->fieldId;

		    $record->lat     = $map->lat;
		    $record->lng     = $map->lng;
		    $record->zoom    = $map->zoom;
		    $record->address = $map->address;
		    $record->parts   = $map->parts;

		    $record->save();
	    }

	    // 4. Update field settings
	    $rows = (new Query())
		    ->select(['id', 'settings'])
		    ->from(Table::FIELDS)
		    ->where(['type' => MapField::class])
		    ->all();

	    foreach ($rows as $row)
	    {
		    $id          = $row['id'];
		    $oldSettings = Json::decodeIfJson($row['settings']);

		    $newSettings = [
			    'lat'     => $oldSettings['lat'],
			    'lng'     => $oldSettings['lng'],
			    'zoom'    => $oldSettings['zoom'],
			    'country' => strtoupper($oldSettings['countryRestriction']),
			    'hideMap' => $oldSettings['hideMap'],
		    ];

		    $this->db->createCommand()
				->update(
					Table::FIELDS,
					[ 'settings' => Json::encode($newSettings) ],
					compact('id')
				)
				->execute();
	    }
    }

    // Helpers
    // =========================================================================

	/**
	 * Returns a site handle based on a given locale.
	 *
	 * @param string $locale
	 *
	 * @return string
	 */
	private function locale2handle (string $locale): string
	{
		if (
			!preg_match('/^' . HandleValidator::$handlePattern . '$/', $locale) ||
			in_array(strtolower($locale), HandleValidator::$baseReservedWords, true)
		) {
			$localeParts = array_filter(preg_split('/[^a-zA-Z0-9]/', $locale));

			return $localeParts ? '_' . implode('_', $localeParts) : '';
		}

		return $locale;
	}

	/**
	 * Gets the new site based off the old locale
	 *
	 * @param string $locale
	 *
	 * @return \craft\models\Site
	 */
	private function getSiteByLocale ($locale)
	{
		$sites = \Craft::$app->sites;

		if ($locale === null)
			return static::$sitesByOldLocale[$locale] = $sites->primarySite;

		if (array_key_exists($locale, static::$sitesByOldLocale))
			return static::$sitesByOldLocale[$locale];

		$handle = $this->locale2handle($locale);

		$siteId = (new Query())
			->select('id')
			->from(Table::SITES)
			->where(['like', 'handle', '%' . $handle])
			->column();

		if (!empty($siteId))
			return static::$sitesByOldLocale[$locale] = $sites->getSiteById($siteId[0]);

		return static::$sitesByOldLocale[$locale] = $sites->primarySite;
	}

}
