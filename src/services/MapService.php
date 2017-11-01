<?php

namespace ether\SimpleMap\services;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use ether\SimpleMap\fields\MapField;
use ether\SimpleMap\models\Map;
use ether\SimpleMap\records\MapRecord;
use ether\SimpleMap\SimpleMap;
use yii\base\Component;
use yii\base\Exception;

class MapService extends Component
{

	// Public Props
	// =========================================================================

	// Public Props: Instance
	// -------------------------------------------------------------------------

	public $searchLatLng;
	public $searchEarthRad;
	public $searchDistanceUnit;

	// Private Props
	// =========================================================================

	// Private Props: Static
	// -------------------------------------------------------------------------

	/** @var string */
	private static $_apiKey;

	// Public Methods
	// =========================================================================

	// Public Methods: Static
	// -------------------------------------------------------------------------

	/**
	 * Converts the given address to Lat/Lng
	 *
	 * @param string $address
	 *
	 * @return array|null
	 */
	public static function getLatLngFromAddress ($address)
	{
		$browserApiKey = self::_getAPIKey();

		if (!$browserApiKey) return null;

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='
		       . rawurlencode($address)
		       . '&key=' . $browserApiKey;

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

		if (empty($resp['results'])) return null;

		return $resp['results'][0]['geometry']['location'];
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

		if (\Craft::$app->request->getIsPost() && $value) {
			$model = new Map($value);
		} else if ($record) {
			$model = new Map($record->getAttributes());
		} else {
			$model = new Map();
		}

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
	 */
	public function saveField (MapField $field, ElementInterface $owner): bool
	{
		/** @var Element $owner */
		$locale = $owner->getSite()->language;

		// FIXME: All instances of `$field` should be pointing to the value
		// (except `$field->id`)

		$lat = number_format((float)$field->lat, 9);
		$lng = number_format((float)$field->lng, 9);

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

		list($field->parts, $field->address) = $this->_getPartsFromLatLng(
			$lat,
			$lng,
			$field->address ?: '',
			$locale
		);

		$record->lat     = $lat;
		$record->lng     = $lng;
		$record->zoom    = $field->zoom;
		$record->address = $field->address;
		$record->parts   = $field->parts;

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
	 * @param MapField              $field
	 * @param ElementQueryInterface $query
	 * @param                       $value
	 *
	 * @return null
	 */
	public function modifyElementsQuery (MapField $field, ElementQueryInterface $query, $value)
	{
		if (!$value) return;
		/** @var ElementQuery $query */

		$query->join(
			'JOIN',
			MapRecord::$tableName,
			"[[elements.id]] = [[{$field->handle}.ownerId]]"
		);

		if (array_key_exists('location', $query->params))
			$this->_searchLocation($query);

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
		if (!$this->searchLatLng || !$this->searchEarthRad) return null;

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
	 * Searches for entries by location
	 *
	 * @param ElementQuery $query
	 */
	private function _searchLocation (ElementQuery $query)
	{
		$params = $query->params;

		$location = $params['location'];
		$radius   = array_key_exists('radius', $params)
						? $params['radius']
						: 50.0;
		$unit     = array_key_exists('unit', $params)
						? $params['unit']
						: 'km';

		if (!is_numeric($radius)) $radius = (float)$radius;
		if (!is_numeric($radius)) $radius = 50.0;

		if (!in_array($unit, array('km', 'mi'))) $unit = 'km';

		if (is_string($location))
			$location = self::getLatLngFromAddress($location);

		if (is_array($location)) {
			if (
				!array_key_exists('lat', $location)
				|| !array_key_exists('lng', $location)
			) $location = null;
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

		$table = MapRecord::$tableName;

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

}