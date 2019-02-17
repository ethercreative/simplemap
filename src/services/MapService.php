<?php

namespace ether\simplemap\services;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use ether\simplemap\fields\MapField;
use ether\simplemap\models\Map;
use ether\simplemap\records\MapRecord;
use ether\simplemap\SimpleMap;
use yii\base\Component;
use yii\base\Exception;

class MapService extends Component
{

	// Public Props
	// =========================================================================

	// Public Props: Instance
	// -------------------------------------------------------------------------

	public $searchLatLng;
	public $searchDistanceUnit;

	// Private Props
	// =========================================================================

	// Private Props: Static
	// -------------------------------------------------------------------------

	/** @var string */
	private static $_apiKey;

	private static $_cachedAddressToLatLngs = [];

	public static $parts = [
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

	// Public Methods
	// =========================================================================

	// Public Methods: Static
	// -------------------------------------------------------------------------

	/**
	 * Converts the given address to Lat/Lng
	 *
	 * @param string      $address
	 * @param string|null $country
	 *
	 * @return array|null
	 * @throws Exception
	 */
	public static function getLatLngFromAddress ($address, $country = null)
	{
		if (array_key_exists($address, self::$_cachedAddressToLatLngs)) {
			return self::$_cachedAddressToLatLngs[$address];
		}

		$apiKey = self::_getAPIKey();

		if (!$apiKey) return null;

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='
		       . rawurlencode($address)
		       . '&key=' . $apiKey;

		if ($country)
			$url .= '&components=country:' . rawurldecode($country);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$resp = json_decode(curl_exec($ch), true);

		if (
			array_key_exists('error_message', $resp)
			&& $resp['error_message']
		) {
			\Craft::getLogger()->log(
				$resp['error_message'],
				LOG_ERR,
				'simplemap'
			);
		}

		if (empty($resp['results'])) $latLng = null;
		else $latLng = $resp['results'][0]['geometry']['location'];

		self::$_cachedAddressToLatLngs[$address] = $latLng;

		return $latLng;
	}

	// Public Methods: Instance
	// -------------------------------------------------------------------------

	/**
	 * Gets the field
	 *
	 * @param MapField         $field
	 * @param ElementInterface $owner
	 * @param                  $value
	 *
	 * @return Map
	 */
	public function getField (MapField $field, ElementInterface $owner, $value): Map
	{
		/** @var Element $owner */

		$record = MapRecord::findOne(
			[
				'ownerId'     => $owner->id,
				'ownerSiteId' => $owner->siteId,
				'fieldId'     => $field->id,
			]
		);

		if (
			!\Craft::$app->request->isConsoleRequest
			&& \Craft::$app->request->isPost
			&& $value
		) {
			$model = new Map($value);
		} else if ($record) {
			$model = new Map($record->getAttributes());
		} else {
			$model = new Map();
		}

		$model->parts = $this->_padParts($model);

		$model->distance = $this->_calculateDistance($model);

		return $model;
	}

	/**
	 * Saves the field
	 *
	 * @param MapField         $field
	 * @param ElementInterface $owner
	 *
	 * @return bool
	 * @throws Exception
	 * @throws \yii\base\InvalidConfigException
	 */
	public function saveField (MapField $field, ElementInterface $owner): bool
	{
		/** @var Element $owner */
		$locale = $owner->getSite()->language;
		/** @var Map $value */
		$value = $owner->getFieldValue($field->handle);

		if (
			(is_null($value->lat) || is_null($value->lng))
			&& !is_null($value->address)
		) {
			if ($addressToLatLng = self::getLatLngFromAddress($value->address)) {
				$value->lat = $addressToLatLng['lat'];
				$value->lng = $addressToLatLng['lng'];
			}
		}

		$lat = number_format((float)$value->lat, 9);
		$lng = number_format((float)$value->lng, 9);

		$record = MapRecord::findOne(
			[
				'ownerId'     => $owner->id,
				'ownerSiteId' => $owner->siteId,
				'fieldId'     => $field->id,
			]
		);

		if (!$record) {
			$record              = new MapRecord();
			$record->ownerId     = $owner->id;
			$record->ownerSiteId = $owner->siteId;
			$record->fieldId     = $field->id;
		}

		list($value->parts, $value->address) = $this->_getPartsFromLatLng(
			$lat,
			$lng,
			$value->address ?: '',
			$locale
		);

		$record->lat     = $lat;
		$record->lng     = $lng;
		$record->zoom    = $value->zoom;
		$record->address = $value->address;
		$record->parts   = $value->parts;

		$save = $record->save();

		if (!$save) {
			\Craft::getLogger()->log(
				$record->getErrors(),
				LOG_ERR,
				'simplemap'
			);
		}

		return $save;
	}

	/**
	 * Modifies the query to inject the field data
	 *
	 * @param ElementQueryInterface $query
	 * @param                       $value
	 *
	 * @return null
	 * @throws Exception
	 */
	public function modifyElementsQuery (ElementQueryInterface $query, $value)
	{
		if (!$value) return;
		/** @var ElementQuery $query */

		$tableName = MapRecord::$tableName;
		$tableAlias = 'simplemap' . bin2hex(openssl_random_pseudo_bytes(5));

		$on = [
			'and',
			'[[elements.id]] = [['.$tableAlias.'.ownerId]]',
			'[[elements_sites.siteId]] = [['.$tableAlias.'.ownerSiteId]]',
		];

		$query->query->join(
			'JOIN',
			"{$tableName} {$tableAlias}",
			$on
		);

		$query->subQuery->join(
			'JOIN',
			"{$tableName} {$tableAlias}",
			$on
		);

		if (!is_array($query->orderBy)) {
			$oldOrderBy = $query->orderBy;
			$query->orderBy = [];
		}

		if (array_key_exists('location', $value)) {
			$this->_searchLocation($query, $value, $tableAlias);
		} else if (array_key_exists('distance', $query->orderBy)) {
			$this->_replaceOrderBy($query);
		}

		if (array_key_exists('oldOrderBy', get_defined_vars()))
			/** @noinspection PhpUndefinedVariableInspection */
			$query->orderBy = $oldOrderBy;

		return;
	}

	// Private Methods
	// =========================================================================

	// Private Methods: Static
	// -------------------------------------------------------------------------

	/**
	 * Gets the API key
	 *
	 * @return string
	 * @throws Exception
	 */
	private static function _getAPIKey ()
	{
		if (self::$_apiKey)
			return self::$_apiKey;

		$apiKey = SimpleMap::$plugin->getSettings()->unrestrictedApiKey;

		if (!$apiKey)
			$apiKey = SimpleMap::$plugin->getSettings()->apiKey;

		if (!$apiKey)
			throw new Exception("SimpleMap missing API key!");

		self::$_apiKey = $apiKey;
		return $apiKey;
	}

	// Private Methods: Instance
	// -------------------------------------------------------------------------

	/**
	 * @param float       $lat
	 * @param float       $lng
	 * @param string      $address
	 * @param string|null $locale
	 *
	 * @return array
	 * @throws Exception
	 */
	private function _getPartsFromLatLng ($lat, $lng, $address, $locale)
	{
		if (!$locale || !is_string($locale)) $locale = 'en';

		$apiKey = self::_getAPIKey();
		$failedReturn = [[], $address];

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='
		       . $lat . ',' . $lng
			   . '&language=' . $this->_formatLocaleForMap($locale)
			   . '&key=' . $apiKey;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$resp = json_decode(curl_exec($ch), true);

		if (curl_errno($ch)) {
			\Craft::getLogger()->log(
				curl_error($ch),
				LOG_ERR,
				'simplemap'
			);

			return $failedReturn;
		}

		if (
			array_key_exists('error_message', $resp)
			&& $resp['error_message']
		) {
			\Craft::getLogger()->log(
				$resp['error_message'],
				LOG_ERR,
				'simplemap'
			);
		}

		if (empty($resp['results']))
			return $failedReturn;

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

	/**
	 * Takes a locale in the format xx_YY and converts it to xx-yy
	 *
	 * @param string $locale
	 *
	 * @return string - The formatted locale
	 */
	private function _formatLocaleForMap ($locale)
	{
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

	/**
	 * Calculates the distance of the location, from the stored search query
	 *
	 * @param Map $map
	 *
	 * @return float|null
	 */
	private function _calculateDistance (Map $map)
	{
		if (!$this->searchLatLng || !$this->searchDistanceUnit) return null;

		$lt1 = $this->searchLatLng['lat'];
		$ln1 = $this->searchLatLng['lng'];

		$lt2 = $map->lat;
		$ln2 = $map->lng;

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
	}

	/**
	 * Fills out the missing parts values
	 *
	 * @param Map $model
	 *
	 * @return array
	 */
	private function _padParts (Map $model)
	{
		$parts = $model->parts ?: [];

		foreach (self::$parts as $part) {
			if (!array_key_exists($part, $parts)) {
				$parts[$part]            = '';
				$parts[$part . '_short'] = '';
			}
		}

		return $parts;
	}

	/**
	 * Searches for entries by location
	 *
	 * @param ElementQuery $query
	 * @param array        $value
	 * @param string       $tableAlias
	 *
	 * @throws Exception
	 */
	private function _searchLocation (ElementQuery $query, $value, $tableAlias)
	{
		$location = $value['location'];
		$country  = array_key_exists('country', $value)
						? $value['country']
						: null;
		$radius   = array_key_exists('radius', $value)
						? $value['radius']
						: 50.0;
		$unit     = array_key_exists('unit', $value)
						? $value['unit']
						: 'km';

		if (!is_numeric($radius)) $radius = (float)$radius;
		if (!is_numeric($radius)) $radius = 50.0;

		if ($unit == 'miles') $unit = 'mi';
		else if ($unit == 'kilometers') $unit = 'km';
		else if (!in_array($unit, ['km', 'mi'])) $unit = 'km';

		if (is_string($location))
			$location = self::getLatLngFromAddress($location, $country);

		if (is_array($location)) {
			if (
				!array_key_exists('lat', $location)
				|| !array_key_exists('lng', $location)
			) $location = null;
		} else {
			$location = null;
		}

		if ($location == null) {
			if (array_key_exists('distance', $query->orderBy)) {
				$this->_replaceOrderBy($query, false);
			}
			return;
		}

		if ($unit == 'km') $distanceUnit = 111.045;
		else $distanceUnit = '69.0'; // String to force float in SQL

		$this->searchLatLng = $location;
		$this->searchDistanceUnit = (float)$distanceUnit;

		$distanceSearch = "(
			$distanceUnit
			* DEGREES(
				ACOS(
					COS(RADIANS($location[lat]))
					* COS(RADIANS([[$tableAlias.lat]]))
					* COS(RADIANS($location[lng]) - RADIANS([[$tableAlias.lng]]))
					+ SIN(RADIANS($location[lat]))
					* SIN(RADIANS([[$tableAlias.lat]]))
				)
			)
		)";

		$distanceSearch = str_replace(["\r", "\n", "\t"], '', $distanceSearch);

		$restrict = [
			'and',
			[
				'and',
				"[[$tableAlias.lat]] >= $location[lat] - ($radius / $distanceUnit)",
				"[[$tableAlias.lat]] <= $location[lat] + ($radius / $distanceUnit)",
			],
			[
				'and',
				"[[$tableAlias.lng]] >= $location[lng] - ($radius / ($distanceUnit * COS(RADIANS($location[lat]))))",
				"[[$tableAlias.lng]] <= $location[lng] + ($radius / ($distanceUnit * COS(RADIANS($location[lat]))))",
			]
		];

		if (array_key_exists('distance', $query->orderBy)) {
			$this->_replaceOrderBy($query, $distanceSearch);
		}

		$query
			->subQuery
				->andWhere($restrict)
				->andWhere("$distanceSearch <= $radius");
	}

	/**
	 * Replaces the *distance* orderBy
	 *
	 * @param ElementQuery $query
	 * @param bool|string  $distanceSearch
	 */
	private function _replaceOrderBy (ElementQuery $query, $distanceSearch = false)
	{
		$nextOrder = [];

		foreach ((array)$query->orderBy as $order => $sort) {
			if ($order == 'distance' && $distanceSearch) $nextOrder[$distanceSearch] = $sort;
			elseif ($order != 'distance') $nextOrder[$order] = $sort;
		}

		$query->orderBy($nextOrder);
	}

}
