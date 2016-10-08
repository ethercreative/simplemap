<?php

namespace Craft;

class SimpleMapService extends BaseApplicationComponent {

	public $settings;

	public $searchLatLng;
	public $searchEarthRad;

	/// PUBLIC ///

	/**
	 * Initialize
	 */
	public function init()
	{
		$this->settings = craft()->plugins->getPlugin('SimpleMap')->getSettings();
	}

	/**
	 * Get Map Field
	 *
	 * @param SimpleMap_MapFieldType $fieldType
	 * @param $value
	 * @return SimpleMap_MapModel
	 */
	public function getField (SimpleMap_MapFieldType $fieldType, $value)
	{
		$owner = $fieldType->element;
		$field = $fieldType->model;;

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

		$model->distance = $this->_calculateDistance($model);

		return $model;
	}

	/**
	 * Save Map Field
	 *
	 * @param SimpleMap_MapFieldType $fieldType
	 * @return bool
	 */
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
			SimpleMapPlugin::log(print_r($record->getErrors(), true), LogLevel::Error);
		}

		return $save;
	}

	/**
	 * Modify Query
	 *
	 * @param DbCommand $query
	 * @param array $params
	 */
	public function modifyQuery (DbCommand &$query, $params = array())
	{
		$query->join(SimpleMap_MapRecord::TABLE_NAME, 'elements.id=' . craft()->db->tablePrefix . SimpleMap_MapRecord::TABLE_NAME . '.ownerId');

		if (array_key_exists('location', $params)) {
			$this->_searchLocation($query, $params);
		}
	}


	/// PRIVATE ///

	/**
	 * Search for entries by location
	 *
	 * @param DbCommand $query
	 * @param array $params
	 */
	private function _searchLocation (DbCommand &$query, $params)
	{
		$location = $params['location'];
		$radius   = array_key_exists('radius', $params) ? $params['radius'] : 50;
		$unit     = array_key_exists('unit', $params) ? $params['unit'] : 'kilometers';

		if (!is_numeric($radius)) $radius = (float)$radius;
		if (!is_numeric($radius)) $radius = 50;

		if (!in_array($unit, array('km', 'mi'))) $unit = 'km';

		if (is_string($location)) $location = $this->_getLatLngFromAddress($location);
		if (is_array($location)) {
			if (!array_key_exists('lat', $location) || !array_key_exists('lng', $location))
				$location = null;
		} else return;

		if ($location === null) return;

		if ($unit === 'km') $earthRad = 6371;
		else $earthRad = 3959;

		$this->searchLatLng = $location;
		$this->searchEarthRad = $earthRad;

		$table = craft()->db->tablePrefix . SimpleMap_MapRecord::TABLE_NAME;

		$haversine = "($earthRad * acos(cos(radians($location[lat])) * cos(radians($table.lat)) * cos(radians($table.lng) - radians($location[lng])) + sin(radians($location[lat])) * sin(radians($table.lat))))";

		$query
			->addSelect($haversine . ' AS distance')
			->having('distance <= ' . $radius);
	}

	/**
	 * Find lat/lng from string address
	 *
	 * @param $address
	 * @return null|array
	 *
	 * TODO: Cache results?
	 */
	private function _getLatLngFromAddress ($address)
	{
		if (!$this->settings['browserApiKey']) return null;

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . rawurlencode($address)
			. '&key=' . $this->settings['browserApiKey'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$resp = json_decode(curl_exec($ch), true);

		if (array_key_exists('error_message', $resp) && $resp['error_message'])
			SimpleMapPlugin::log($resp['error_message'], LogLevel::Error);

		if (empty($resp['results'])) return null;

		return $resp['results'][0]['geometry']['location'];
	}

	private function _calculateDistance (SimpleMap_MapModel $model)
	{
		if (!$this->searchLatLng || !$this->searchEarthRad) return null;

		$lt1 = $this->searchLatLng['lat'];
		$ln1 = $this->searchLatLng['lng'];

		$lt2 = $model->lat;
		$ln2 = $model->lng;

		return ($this->searchEarthRad * acos(cos(deg2rad($lt1)) * cos(deg2rad($lt2)) * cos(deg2rad($ln2) - deg2rad($ln1)) + sin(deg2rad($lt1)) * sin(deg2rad($lt2))));
	}

}