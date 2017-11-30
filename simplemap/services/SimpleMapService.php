<?php

namespace Craft;

class SimpleMapService extends BaseApplicationComponent {

	private static $apiKey;

	public $settings;

	public $searchLatLng;
	public $searchEarthRad;
	public $searchDistanceUnit;

	private static $_parts = [
		'room',
		'floor',
		'establishment',
		'subpremise',
		'premise',
		'street_number',
		'postal_code',
		'street_address',
		'colloquial_area',
		'neighborhood',
		'route',
		'intersection',
		'postal_town',
		'sublocality_level_5',
		'sublocality_level_4',
		'sublocality_level_3',
		'sublocality_level_2',
		'sublocality_level_1',
		'sublocality',
		'locality',
		'political',
		'administrative_area_level_5',
		'administrative_area_level_4',
		'administrative_area_level_3',
		'administrative_area_level_2',
		'administrative_area_level_1',
		'ward',
		'country',
		'parking',
		'post_box',
		'point_of_interest',
		'natural_feature',
		'park',
		'airport',
		'bus_station',
		'train_station',
		'transit_station',
	];

	// Public
	// =========================================================================

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
		$field = $fieldType->model;
		$locale = $field->translatable ? $owner->locale : null;

		$record = SimpleMap_MapRecord::model()->findByAttributes(array(
			'ownerId'     => $owner->id,
			'fieldId'     => $field->id,
			'ownerLocale' => $locale
		));

		if (!$record) {
			$record = SimpleMap_MapRecord::model()->findByAttributes(array(
				'ownerId'     => $owner->id,
				'fieldId'     => $field->id,
				'ownerLocale' => craft()->locale->id
			));
		}

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

		$model->parts = $this->_padParts($model);

		$model->distance = $this->_calculateDistance($model);

		return $model;
	}

	/**
	 * Validates the field
	 *
	 * @param SimpleMap_MapFieldType $fieldType
	 *
	 * @return bool
	 */
	public function validateField (SimpleMap_MapFieldType $fieldType)
	{
		$owner = $fieldType->element;
		$field = $fieldType->model;
		$content = $fieldType->element->getContent();

		$handle = $field->handle;
		$data = $content->getAttribute($handle);

		if (
			!array_key_exists('lat', $data)
			|| !array_key_exists('lng', $data)
		) {
			if (!array_key_exists('address', $data)) {
				$owner->addError($handle, 'Missing lat/lng');
				return false;
			}

			$addressToLatLng = self::getLatLngFromAddress($data['address']);
			if ($addressToLatLng == null) {
				$owner->addError($handle, 'Missing lat/lng or valid address');
				return false;
			}

			$data['lat'] = $addressToLatLng['lat'];
			$data['lng'] = $addressToLatLng['lng'];
		}

		$content->setAttribute($handle, $data);
		return true;
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
		$locale = $field->translatable ? $owner->locale : null;
		$content = $fieldType->element->getContent();

		$handle = $field->handle;
		$data = $content->getAttribute($handle);

		if (!$data) return false;

		$data['lat'] = number_format(floatval($data['lat']), 8);
		$data['lng'] = number_format(floatval($data['lng']), 8);

		$record = SimpleMap_MapRecord::model()->findByAttributes(array(
			'ownerId'     => $owner->id,
			'fieldId'     => $field->id,
			'ownerLocale' => $locale
		));

		list($data['parts'], $data['address']) = $this->_getPartsFromLatLng(
			$data['lat'],
			$data['lng'],
			array_key_exists("address", $data) ? $data['address'] : "",
			$locale
		);

		if (!$record) {
			$record = new SimpleMap_MapRecord;
			$record->ownerId     = $owner->id;
			$record->fieldId     = $field->id;
			$record->ownerLocale = $locale;
		}

		$record->setAttributes($data, false);

		$save = $record->save();

		if (!$save) {
			SimpleMapPlugin::log(
				print_r($record->getErrors(), true), LogLevel::Error
			);
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


	// Private
	// =========================================================================

	/**
	 * Search for entries by location
	 *
	 * @param DbCommand $query
	 * @param array $params
	 */
	private function _searchLocation (DbCommand &$query, $params)
	{
		$location = $params['location'];
		$country  = array_key_exists('country', $params) ? $params['country'] : null;
		$radius   = array_key_exists('radius', $params) ? $params['radius'] : 50.0;
		$unit     = array_key_exists('unit', $params) ? $params['unit'] : 'kilometers';

		if (!is_numeric($radius)) $radius = (float)$radius;
		if (!is_numeric($radius)) $radius = 50.0;

		if (!in_array($unit, array('km', 'mi'))) $unit = 'km';

		if (is_string($location))
			$location = self::getLatLngFromAddress($location, $country);
		if (is_array($location)) {
			if (!array_key_exists('lat', $location) ||
			    !array_key_exists('lng', $location))
				$location = null;
		} else {
			$location = null;
		}

		if ($location == null) {
			$query->addSelect("(0) AS distance");
			return;
		}

		if ($unit == 'km') $distanceUnit = 111.045;
		else $distanceUnit = 69.0;

		$this->searchLatLng = $location;
		$this->searchDistanceUnit = $distanceUnit;

		$table = craft()->db->tablePrefix . SimpleMap_MapRecord::TABLE_NAME;

		$haversine = "
(
	$distanceUnit
	* DEGREES(
		ACOS(
			COS(RADIANS($location[lat]))
			* COS(RADIANS($table.lat))
			* COS(RADIANS($location[lng]) - RADIANS($table.lng))
			+ SIN(RADIANS($location[lat]))
			* SIN(RADIANS($table.lat))
		)
	)
)
";

		$restrict = [
			'and',
			[
				'and',
				"$table.lat >= $location[lat] - ($radius / $distanceUnit)",
				"$table.lat <= $location[lat] + ($radius / $distanceUnit)",
			],
			[
				'and',
				"$table.lng >= $location[lng] - ($radius / ($distanceUnit * COS(RADIANS($location[lat]))))",
				"$table.lng <= $location[lng] + ($radius / ($distanceUnit * COS(RADIANS($location[lat]))))",
			]
		];

		$query
			->addSelect($haversine . ' AS distance')
			->andWhere($restrict)
			->having('distance <= ' . $radius);
	}

	/**
	 * Find lat/lng from string address
	 *
	 * @param $address
	 * @param string|null $country
	 *
	 * @return null|array
	 *
	 * TODO: Cache results?
	 */
	public static function getLatLngFromAddress ($address, $country = null)
	{
		$browserApiKey = self::getAPIKey();

		if (!$browserApiKey) return null;

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='
		       . rawurlencode($address)
		       . '&key=' . $browserApiKey;

		if ($country)
			$url .= '&components=country:' . rawurldecode($country);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$resp = json_decode(curl_exec($ch), true);

		if (array_key_exists('error_message', $resp) && $resp['error_message'])
			SimpleMapPlugin::log($resp['error_message'], LogLevel::Error);

		if (empty($resp['results'])) return null;

		return $resp['results'][0]['geometry']['location'];
	}

	/**
	 * Get the address parts for the selected location, matching the
	 * textual address where possible.
	 *
	 * @param double $lat
	 * @param double $lng
	 * @param string $address
	 * @param string $locale
	 *
	 * @return array
	 */
	private function _getPartsFromLatLng ($lat, $lng, $address, $locale)
	{
		$browserApiKey = self::getAPIKey();
		$failedReturn = [[], $address];

		if (!$browserApiKey) return $failedReturn;

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='
		       . $lat . ',' . $lng
		       . '&language=' . $this->_formatLocaleForMap($locale)
		       . '&key=' . $browserApiKey;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$resp = json_decode(curl_exec($ch), true);

		if (curl_errno($ch)) {
			SimpleMapPlugin::log(curl_error($ch), LogLevel::Error);
			return $failedReturn;
		}

		if (array_key_exists('error_message', $resp) && $resp['error_message'])
			SimpleMapPlugin::log($resp['error_message'], LogLevel::Error);

		if (empty($resp['results'])) return $failedReturn;

		$result = $resp['results'][0];
		$formattedAddress = empty($address)
			? $result['formatted_address']
			: $address;

		foreach ($resp['results'] as $res) {
			if ($res['formatted_address'] == $address) {
				$result = $res;
				break;
			}
		}

		$parts = [];
		$rawParts = $result['address_components'];

		foreach ($rawParts as $part) {
			$n = $part['types'][0];

			if (!$n) continue;

			if ($n == 'postal_code_prefix') $n = 'postal_code';

			$parts[$n] = $part['long_name'];
			$parts[$n . "_short"] = $part['short_name'];
		}

		return [$parts, $formattedAddress];
	}

	private function _calculateDistance (SimpleMap_MapModel $model)
	{
		if (!$this->searchLatLng || !$this->searchEarthRad) return null;

		$lt1 = $this->searchLatLng['lat'];
		$ln1 = $this->searchLatLng['lng'];

		$lt2 = $model->lat;
		$ln2 = $model->lng;

		return (
			$this->searchDistanceUnit
			* rad2deg(
				acos(
					cos(deg2rad($lt1))
					* cos(deg2rad($lt2))
					* cos(deg2rad($ln1) - deg2rad($ln2))
					+ sin(deg2rad($lt1))
					* sin(deg2rad($lt2))
				)
			)
		);
//		return ($this->searchEarthRad * acos(cos(deg2rad($lt1)) * cos(deg2rad($lt2)) * cos(deg2rad($ln2) - deg2rad($ln1)) + sin(deg2rad($lt1)) * sin(deg2rad($lt2))));
	}

	private function _padParts (SimpleMap_MapModel $model)
	{
		$parts = $model->parts ?: [];

		foreach (self::$_parts as $part) {
			if (!array_key_exists($part, $parts)) {
				$parts[$part]            = '';
				$parts[$part . '_short'] = '';
			}
		}

		return $parts;
	}

	private function _formatLocaleForMap ($locale) {
		$locale = array_map(
			'strtolower',
			explode("_", $locale)
		);

		if (count($locale) == 1)
			return $locale[0];

		/**
		 * Locales that have different, supported, dialects
		 *
		 * @see https://developers.google.com/maps/faq#languagesupport
		 */
		$allowedSpecifics = [
			"en","au","gb", // English
			"pt","br",      // Portuguese
			"zh","cn","tw", // Chinese
		];

		if (
			in_array($locale[0], $allowedSpecifics) &&
			in_array($locale[1], $allowedSpecifics)
		) return $locale[0] . "-" . strtoupper($locale[1]);

		return $locale[0];
	}

	private static function getAPIKey ()
	{
		if (self::$apiKey)
			return self::$apiKey;

		$apiKey = craft()->plugins
			          ->getPlugin('SimpleMap')
			          ->getSettings()['serverApiKey'];

		if (!$apiKey) {
			$apiKey = craft()->plugins
				          ->getPlugin('SimpleMap')
				          ->getSettings()['browserApiKey'];
		}

		if (!$apiKey) {
			SimpleMapPlugin::log("Missing API Key", LogLevel::Error);
			$apiKey = "";
		}

		self::$apiKey = $apiKey;
		return self::$apiKey;
	}

}
