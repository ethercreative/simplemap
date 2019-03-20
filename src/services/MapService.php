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
use ether\simplemap\fields\MapField;
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

	// Properties
	// =========================================================================

	private $_location;
	private $_distance;

	// Methods
	// =========================================================================

	/**
	 * @param MapField                 $field
	 * @param ElementInterface|Element $element
	 *
	 * @throws \Throwable
	 * @throws \yii\db\Exception
	 */
	public function saveField (MapField $field, ElementInterface $element)
	{
		if ($element instanceof MapElement)
			return;

		$craft = \Craft::$app;

		$transaction = $craft->getDb()->beginTransaction();

		try
		{
			/** @var MapElement $value */
			$value = $element->getFieldValue($field->handle);

			if (!$craft->elements->saveElement($value))
			{
				foreach ($value->getErrors() as $error)
					$element->addError($field->handle, $error[0]);

				$transaction->rollBack();
				return;
			}

			$record = null;

			if ($value->elementId)
			{
				$record = MapRecord::findOne([
					'elementId' => $value->elementId,
					'ownerSiteId' => $value->ownerSiteId,
				]);
			}

			if ($record === null)
			{
				$record = new MapRecord();

				$record->elementId = $value->elementId;
				$record->ownerId = $element->id;
				$record->ownerSiteId = $element->site->id;
				$record->fieldId = $field->id;
			}

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

		$table = MapRecord::TableNameClean;
		$alias = $table . '_' . bin2hex(openssl_random_pseudo_bytes(5));
		$on = [
			'and',
			'[[elements.id]] = [[' . $alias . '.ownerId]]',
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
		else if (!is_array($location) || !isset($location['lat'], $location['lng']))
			$location = null;

		if ($location === null)
			return false;

		list('lat' => $lat, 'lng' => $lng) = $location;

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

}