<?php

namespace ether\simplemap\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use craft\helpers\Json;
use craft\services\Plugins;
use craft\validators\HandleValidator;
use ether\simplemap\enums\GeoService;
use ether\simplemap\enums\MapTiles;
use ether\simplemap\models\Settings;
use ether\simplemap\models\Map;
use ether\simplemap\records\Map as MapRecord;
use ether\simplemap\fields\MapField;
use ether\simplemap\SimpleMap;

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
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        // 1. Run the install migration
	    if (!$this->db->tableExists(MapRecord::OldTableName) && !$this->db->tableExists(MapRecord::TableName))
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
	 * @throws \yii\base\Exception
	 * @throws \yii\db\Exception
	 */
    private function _upgrade2 ()
    {
    	$mapService = SimpleMap::getInstance()->map;
		$fieldsService = Craft::$app->getFields();

    	// Delete the old plugin row
	    $this->delete(Table::PLUGINS, ['handle' => 'simple-map']);

	    // Update the old data
	    echo '    > Start map data upgrade' . PHP_EOL;

	    $rows = (new Query())
		    ->select('*')
		    ->from('{{%simplemap_maps}}')
		    ->all();

	    foreach ($rows as $row)
	    {
		    echo '    > Upgrade map value ' . $row['address'] . PHP_EOL;

	    	$site = $this->getSiteByLocale($row['ownerLocale']);

		    $map              = new Map();
		    $map->ownerId     = $row['ownerId'];
		    $map->ownerSiteId = $site->id;
		    $map->fieldId     = $row['fieldId'];
		    $map->lat         = $row['lat'];
		    $map->lng         = $row['lng'];

		    $mapService->saveRecord($map, true);
	    }

	    $this->dropTable('{{%simplemap_maps}}');

	    // Update old field types
		echo '    > Upgrade map field type upgrade' . PHP_EOL;

		$fieldContexts = [];
		$fieldContextsData = (new \craft\db\Query())
			->select(['context'])
			->from(['{{%fields}}'])
			->all();
		foreach ($fieldContextsData as $fieldData) {
			$fieldContexts[] = $fieldData['context'];
		}
		$fieldContexts = array_unique($fieldContexts);

		$fields = $fieldsService->getAllFields($fieldContexts);
		foreach ($fields as $field)
		{
			if ($field instanceof \craft\fields\MissingField && $field->expectedType === 'SimpleMap_Map') {
				echo '    > Upgrade map field ' . $field->handle . PHP_EOL;

				$oldSettings = Json::decodeIfJson($field->settings);

				$newField = new MapField([
					'id'                   => $field->id,
					'groupId'              => $field->groupId,
					'name'                 => $field->name,
					'handle'               => $field->handle,
					'instructions'         => $field->instructions,
					'searchable'           => $field->searchable,
					'translationMethod'    => $field->translationMethod,
					'translationKeyFormat' => $field->translationKeyFormat,

					'lat'     => $oldSettings['lat'],
					'lng'     => $oldSettings['lng'],
					'zoom'    => $oldSettings['zoom'] ?? 15,
					'country' => strtoupper($oldSettings['countryRestriction'] ?? '') ?: null,
					'hideMap' => $oldSettings['hideMap'],
				]);

				$fieldsService->saveField($newField);
			}
	    }

	    // Update the plugin settings
	    $this->updatePluginSettings();
    }

	/**
	 * Upgrade from SimpleMap (3.3.x)
	 *
	 * @throws \Throwable
	 * @throws \yii\base\Exception
	 */
    private function _upgrade3 ()
    {
	    $mapService = SimpleMap::getInstance()->map;

	    // 1. Store the old data
	    echo '    > Start map data upgrade' . PHP_EOL;

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
		    ->from(MapRecord::OldTableName)
		    ->all();

	    // 2. Re-create the table
	    $this->dropTable(MapRecord::OldTableName);

	    if (!$this->db->tableExists(MapRecord::TableName))
	        (new Install())->safeUpPre34();

	    // 3. Store the old data as new
	    $dupeKeys = [];
	    foreach ($rows as $row)
	    {
		    $key = $row['ownerId'] . '_' . $row['ownerSiteId'] . '_' . $row['fieldId'];

		    if (in_array($key, $dupeKeys))
			    continue;

		    $dupeKeys[] = $key;

		    echo '    > Upgrade map value ' . $row['address'] . PHP_EOL;

		    $map              = new Map($row);
		    $map->ownerId     = $row['ownerId'];
		    $map->ownerSiteId = $row['ownerSiteId'];
		    $map->fieldId     = $row['fieldId'];

		    if (!$map->zoom)
		    	$map->zoom = 15;

		    $mapService->saveRecord($map, true);
	    }

	    // 4. Update field settings
	    echo '    > Upgrade map field type upgrade' . PHP_EOL;

	    $rows = (new Query())
		    ->select(['id', 'settings', 'handle'])
		    ->from(Table::FIELDS)
		    ->where(['type' => MapField::class])
		    ->all();

	    foreach ($rows as $row)
	    {
		    echo '    > Upgrade map field ' . $row['handle'] . PHP_EOL;

		    $id          = $row['id'];
		    $oldSettings = Json::decodeIfJson($row['settings']);

		    $newSettings = [
			    'lat'     => $oldSettings['lat'],
			    'lng'     => $oldSettings['lng'],
			    'zoom'    => $oldSettings['zoom'] ?? 15,
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

	    $this->updatePluginSettings();
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

	/**
	 * Updates the plugins settings
	 * @throws \craft\errors\InvalidPluginException
	 */
	private function updatePluginSettings ()
	{
		echo '    > Upgrade Maps settings' . PHP_EOL;

		/** @var Settings $settings */
		$settings = SimpleMap::getInstance()->getSettings()->toArray();
		$newSettings = SimpleMap::getInstance()->getSettings()->toArray();

		$craft2Settings = \Craft::$app->projectConfig->get(
			Plugins::CONFIG_PLUGINS_KEY . '.simple-map.settings'
		);

		if (is_array($craft2Settings) && !empty($craft2Settings))
		{
			$settings = [
				'apiKey' => @$craft2Settings['browserApiKey'] ?: '',
				'unrestrictedApiKey' => @$craft2Settings['serverApiKey'] ?: '',
			];
		}

		if ($settings['unrestrictedApiKey'])
		{
			$newSettings['geoService'] = GeoService::GoogleMaps;
			$newSettings['geoToken'] = $settings['unrestrictedApiKey'];
		}

		if ($settings['apiKey'])
		{
			$newSettings['mapTiles'] = MapTiles::GoogleRoadmap;
			$newSettings['mapToken'] = $settings['apiKey'];

			if (!$settings['unrestrictedApiKey'])
			{
				$newSettings['geoService'] = GeoService::GoogleMaps;
				$newSettings['geoToken'] = $settings['apiKey'];
			}
		}

		\Craft::$app->plugins->savePluginSettings(
			SimpleMap::getInstance(),
			$newSettings
		);

		\Craft::$app->plugins->enablePlugin(SimpleMap::getInstance()->handle);
	}

}
