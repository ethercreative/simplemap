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
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use ether\simplemap\elements\Map;
use ether\simplemap\elements\Map as MapElement;
use ether\simplemap\fields\MapField;
use ether\simplemap\records\Map as MapRecord;

/**
 * Class MapService
 *
 * @author  Ether Creative
 * @package ether\simplemap\services
 */
class MapService extends Component
{

	// Properties
	// =========================================================================

	private $_location;
	private $_distance;

	// Methods
	// =========================================================================

	/**
	 * @param MapField         $field
	 * @param ElementInterface|Element $owner
	 *
	 * @return bool
	 */
	public function validateField (MapField $field, ElementInterface $owner)
	{
		/** @var MapElement $map */
		$map = $owner->getFieldValue($field->handle);

		$valid = $map->validate();

		foreach ($map->getErrors() as $error)
			$owner->addError($field->handle, $error[0]);

		return $valid;
	}

	/**
	 * @param MapField                 $field
	 * @param ElementInterface|Element $owner
	 *
	 * @throws \Throwable
	 */
	public function saveField (MapField $field, ElementInterface $owner)
	{
		if ($owner instanceof MapElement)
			return;

		/** @var MapElement $map */
		$map = $owner->getFieldValue($field->handle);

		$map->fieldId     = $field->id;
		$map->ownerId     = $owner->id;
		$map->ownerSiteId = $owner->siteId;

		\Craft::$app->elements->saveElement($map, true, true);
	}

	/**
	 * @param MapField         $field
	 * @param ElementInterface $owner
	 *
	 * @throws \Throwable
	 */
	public function softDeleteField (MapField $field, ElementInterface $owner)
	{
		/** @var MapElement $map */
		$map = $owner->getFieldValue($field->handle);

		\Craft::$app->getElements()->deleteElement($map);
	}

	/**
	 * @param MapField         $field
	 * @param ElementInterface $owner
	 *
	 * @throws \Throwable
	 * @throws \yii\base\Exception
	 */
	public function restoreField (MapField $field, ElementInterface $owner)
	{
		/** @var MapElement $map */
		$map = $owner->getFieldValue($field->handle);

		\Craft::$app->getElements()->restoreElement($map);
	}

	/**
	 * @param MapElement $map
	 * @param            $ownerId
	 * @param            $ownerSiteId
	 * @param            $fieldId
	 * @param            $isNew
	 *
	 * @throws \yii\db\Exception
	 * @throws \Exception
	 */
	public function saveRecord (MapElement $map, $ownerId, $ownerSiteId, $fieldId, $isNew)
	{
		if ($isNew)
		{
			$record = null;
		}
		else
		{
			$record = MapRecord::findOne($map->id);

			if (!$record)
				throw new \Exception('Invalid map ID: ' . $map->id);
		}

		if ($record === null)
		{
			$record = new MapRecord();

			$record->id          = $map->id;
			$record->ownerId     = $ownerId;
			$record->ownerSiteId = $ownerSiteId;
			$record->fieldId     = $fieldId;
		}

		$record->lat     = $map->lat;
		$record->lng     = $map->lng;
		$record->zoom    = $map->zoom;
		$record->address = $map->address;
		$record->parts   = $map->parts;

		$this->_populateMissingData($record);

		$record->save(false);
	}

	/**
	 * Returns the distance from the search origin (if one exists)
	 *
	 * @param MapElement $map
	 *
	 * @return float|int|null
	 */
	public function getDistance (MapElement $map)
	{
		if (!$this->_location || !$this->_distance)
			return null;

		$originLat = $this->_location['lat'];
		$originLng = $this->_location['lng'];

		$targetLat = $map->lat;
		$targetLng = $map->lng;

		return (
			$this->_distance *
			rad2deg(
				acos(
					cos(deg2rad($originLat)) *
					cos(deg2rad($targetLat)) *
					cos(deg2rad($originLng) - deg2rad($targetLng)) +
					sin(deg2rad($originLat)) *
					sin(deg2rad($targetLat))
				)
			)
		);
	}

	/**
	 * @param ElementQueryInterface $query
	 * @param mixed                 $value
	 *
	 * @throws \yii\db\Exception
	 */
	public function modifyElementsQuery (ElementQueryInterface $query, $value)
	{
		if (empty($value))
			return;

		/** @var ElementQuery $query */

		$table = MapRecord::TableName;
		$alias = MapRecord::TableNameClean . '_' . bin2hex(openssl_random_pseudo_bytes(5));
		$on = [
			'and',
			'[[elements.id]] = [[' . $alias . '.ownerId]]',
			'[[elements.dateDeleted]] IS NULL',
			'[[elements_sites.siteId]] = [[' . $alias . '.ownerSiteId]]',
		];

		$query->query->join('JOIN', $table . ' ' . $alias, $on);
		$query->subQuery->join('JOIN', $table . ' ' . $alias, $on);

		$oldOrderBy = null;
		$search = false;

		if (!is_array($query->orderBy))
		{
			$oldOrderBy = $query->orderBy;
			$query->orderBy = [];
		}

		if (array_key_exists('location', $value))
			$search = $this->_searchLocation($query, $value, $alias);

		if (array_key_exists('distance', $query->orderBy))
			$this->_replaceOrderBy($query, $search);

		if ($oldOrderBy !== null)
			$query->orderBy = $oldOrderBy;
	}

	// Private Methods
	// =========================================================================

	/**
	 * Filters the query by location.
	 *
	 * Returns either `false` if we can't filter by location, or the location
	 * search string if we can.
	 *
	 * @param ElementQuery $query
	 * @param mixed        $value
	 * @param string       $table
	 *
	 * @return bool|string
	 * @throws \yii\db\Exception
	 */
	private function _searchLocation (ElementQuery $query, $value, $table)
	{
		$location = $value['location'];
		$country  = $value['country'] ?? null;
		$radius   = $value['radius'] ?? 50.0;
		$unit     = $value['unit'] ?? 'km';

		// Normalize location
		if (is_string($location))
			$location = GeoService::latLngFromAddress($location, $country);
		else if ($location instanceof Map)
			$location = ['lat' => $location->lat, 'lng' => $location->lng];
		else if (!is_array($location) || !isset($location['lat'], $location['lng']))
			$location = null;

		if ($location === null)
			return false;

		$lat = $location['lat'];
		$lng = $location['lng'];

		// Normalize radius
		if (!is_numeric($radius))
			$radius = (float) $radius;

		if (!is_numeric($radius))
			$radius = 50.0;

		// Normalize unit
		if ($unit === 'miles') $unit = 'mi';
		else if ($unit === 'kilometers') $unit = 'km';
		else if (!in_array($unit, ['mi', 'km'])) $unit = 'km';

		// Base Distance
		$distance = $unit === 'km' ? '111.045' : '69.0';

		// Store for populating search result distance
		$this->_location = $location;
		$this->_distance = (float) $distance;

		// Search Query
		$search = str_replace(["\r", "\n", "\t"], '', "(
			$distance *
			DEGREES(
				ACOS(
					COS(RADIANS($lat)) *
					COS(RADIANS([[$table.lat]])) *
					COS(RADIANS($lng) - RADIANS([[$table.lng]])) +
					SIN(RADIANS($lat)) *
					SIN(RADIANS([[$table.lat]]))
				)
			)
		)");

		// Restrict the results
		$restrict = [
			'and',
			[
				'and',
				"[[$table.lat]] >= $lat - ($radius / $distance)",
				"[[$table.lat]] <= $lat + ($radius / $distance)",
			],
			[
				'and',
				"[[$table.lng]] >= $lng - ($radius / ($distance * COS(RADIANS($lat))))",
				"[[$table.lng]] <= $lng + ($radius / ($distance * COS(RADIANS($lat))))",
			]
		];

		// Filter the query
		$query
			->subQuery
			->andWhere($restrict)
			->andWhere($search . ' <= ' . $radius);

		return $search;
	}

	/**
	 * Will replace the distance search with the correct query if available,
	 * or otherwise remove it.
	 *
	 * @param ElementQuery $query
	 * @param bool         $search
	 */
	private function _replaceOrderBy (ElementQuery $query, $search = false)
	{
		$nextOrder = [];

		foreach ((array) $query->orderBy as $order => $sort)
		{
			if ($order === 'distance' && $search) $nextOrder[$search] = $sort;
			elseif ($order !== 'distance') $nextOrder[$order] = $sort;
		}

		$query->orderBy($nextOrder);
	}

	/**
	 * Populates any missing location data
	 *
	 * @param MapRecord $record
	 *
	 * @throws \yii\db\Exception
	 */
	private function _populateMissingData (MapRecord $record)
	{
		// Missing Lat / Lng
		if (!($record->lat && $record->lng) && $record->address)
		{
			$latLng = GeoService::latLngFromAddress($record->address);
			$record->lat = $latLng['lat'];
			$record->lng = $latLng['lng'];
		}

		// Missing address / parts
		if (!$record->address && ($record->lat && $record->lng))
		{
			$loc = GeoService::addressFromLatLng($record->lat, $record->lng);
			$record->address = $loc['address'];
			$record->parts   = array_merge(
				array_filter((array) $loc['parts']),
				array_filter((array) $record->parts)
			);
		}
	}

}
