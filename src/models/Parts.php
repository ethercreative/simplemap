<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use craft\helpers\Json;
use ether\simplemap\enums\GeoService;
use yii\base\BaseObject;

/**
 * Class Parts
 *
 * @author  Ether Creative
 * @package ether\simplemap\models
 */
class Parts extends BaseObject
{

	// Properties
	// =========================================================================

	/** @var string */
	public $number = '';

	/** @var string */
	public $address = '';

	/** @var string */
	public $city = '';

	/** @var string */
	public $postcode = '';

	/** @var string */
	public $county = '';

	/** @var string */
	public $state = '';

	/** @var string */
	public $country = '';

	/** @var string */
	public $planet = 'Earth';

	/** @var string */
	public $system = 'the Solar System';

	/** @var string */
	public $arm = 'Orion Arm';

	/** @var string */
	public $galaxy = 'Milky Way';

	/** @var string */
	public $group = 'the Local Group';

	/** @var string */
	public $cluster = 'Virgo Cluster';

	/** @var string */
	public $supercluster = 'Laniakea Supercluster';

	// Methods
	// =========================================================================

	public function __construct ($parts = null, string $service = null)
	{
		parent::__construct();

		if ($parts === null)
			return $this;

		if (is_string($parts))
			$parts = Json::decodeIfJson($parts);

		switch ($service)
		{
			case GeoService::Nominatim:
				$this->_nominatim($parts);
				break;
			case GeoService::Mapbox:
				$this->_mapbox($parts);
				break;
			case GeoService::GoogleMaps:
				$this->_google($parts);
				break;
			case GeoService::Here:
				$this->_here($parts);
				break;
			default:
				$this->_fromArray($parts);
		}
	}

	public function getStreetAddress ()
	{
		return $this->address;
	}

	// Methods: Private
	// -------------------------------------------------------------------------

	/**
	 * Parse Nominatim parts
	 *
	 * @param array $parts
	 */
	private function _nominatim (array $parts)
	{
		// Add any missing values
		$keys = [
			'house_number',
			'address29',
			'type',
			'pedestrian',
			'footway',
			'path',
			'road',
			'neighbourhood',
			'suburb',
			'village',
			'town',
			'city_district',
			'city',
			'postcode',
			'county',
			'state_district',
			'state',
			'country',
		];

		foreach ($keys as $key)
			if (!array_key_exists($key, $parts))
				$parts[$key] = null;

		$this->number = $this->_join([
			$parts['house_number'],
			$parts['address29'],
			in_array($parts['type'], [
				'pedestrian',
				'footway',
				'path',
				'road',
				'neighbourhood',
				'suburb',
				'village',
				'town',
				'city_district',
				'city',
			]) ? $parts[$parts['type']] : null,
		]);

		$this->address = $this->_join([
			$parts['pedestrian'],
			$parts['footway'],
			$parts['path'],
			$parts['road'],
			$parts['neighbourhood'],
			$parts['suburb'],
		]);

		$this->city = $this->_join([
			$parts['village'],
			$parts['town'],
			$parts['city_district'],
			$parts['city'],
		]);

		$this->postcode = $parts['postcode'];
		$this->county = $parts['county'];

		$this->state = $this->_join([
			$parts['state_district'],
			$parts['state'],
		]);

		$this->country = $parts['country'];
	}

	/**
	 * Parse Mapbox parts
	 *
	 * @param array $parts
	 */
	private function _mapbox (array $parts)
	{
		$parts = array_reduce(
			$parts['context'],
			function ($a, $part) {
				$key     = explode('.', $part['id'])[0];
				$a[$key] = $part['text'];

				return $a;
			},
			[
				'number' => @$parts['address'],
				$parts['place_type'][0] => $parts['text'],
			]
		);

		$this->number   = @$parts['number'];
		$this->address  = @$parts['address'];
		$this->city     = @$parts['city'];
		$this->postcode = @$parts['postcode'];
		$this->county   = @$parts['county'];
		$this->state    = @$parts['state'];
		$this->country  = @$parts['country'];
	}

	/**
	 * Parse Google parts
	 *
	 * @param $parts
	 */
	private function _google ($parts)
	{
		if (!$this->_isAssoc($parts))
		{
			$parts = array_reduce(
				$parts,
				function ($a, $part) {
					$key     = $part['types'][0];
					$a[$key] = $part['long_name'];

					return $a;
				},
				[]
			);
		}

		foreach (PartsLegacy::$legacyKeys as $key)
			if (!array_key_exists($key, $parts))
				$parts[$key] = '';

		$this->number = $parts['number'] ?? $this->_join([
			$parts['subpremise'],
			$parts['premise'],
			$parts['street_number'],
		]);

		$this->address = $parts['address'] ?? $this->_join([
			$parts['route'],
			$parts['neighborhood'],
			$parts['sublocality_level_5'],
			$parts['sublocality_level_4'],
			$parts['sublocality_level_3'],
			$parts['sublocality_level_2'],
			$parts['sublocality_level_1'],
			$parts['sublocality'],
		]);

		$this->city = $parts['city'] ?? $this->_join([
			$parts['postal_town'],
			$parts['locality'],
		]);

		$this->postcode = $parts['postcode'] ?? $parts['postal_code'] ?? $parts['postal_code_prefix'];
		$this->county = $parts['county'] ?? $parts['administrative_area_level_2'];
		$this->state = $parts['state'] ?? $parts['administrative_area_level_1'];
		$this->country = $parts['country'];
	}

	/**
	 * Parse Here parts
	 *
	 * @param $parts
	 */
	private function _here ($parts)
	{
		$parts = array_merge(
			$parts,
			array_reduce($parts['additionalData'], function ($a, $b) {
				$a[$b['key']] = $b['value'];
				return $a;
			}, [])
		);

		$this->number   = $parts['number'];
		$this->address  = $this->_join([
			$parts['street'],
			$parts['district'],
		]);
		$this->city     = $parts['city'];
		$this->postcode = $parts['postalCode'];
		$this->county   = $parts['CountyName'] ?? $parts['county'];
		$this->state    = $parts['StateName'] ?? $parts['state'];
		$this->country  = $parts['CountryName'] ?? $parts['country'];
	}

	// Methods: Helpers
	// -------------------------------------------------------------------------

	/**
	 * Determines if the given array of parts contains legacy data
	 *
	 * @param array $parts
	 *
	 * @return bool
	 */
	public static function isLegacy (array $parts = null)
	{
		if ($parts === null)
			return false;

		$keys = PartsLegacy::$legacyKeys;

		unset($keys[array_search('country', $keys)]);

		foreach ($keys as $key)
			if (isset($parts[$key]) || array_key_exists($key, $parts))
				return true;

		return false;
	}

	/**
	 * Filters and joins the given array
	 *
	 * @param array $parts
	 *
	 * @return string
	 */
	private function _join (array $parts)
	{
		return implode(', ', array_filter($parts));
	}

	/**
	 * Populates Parts from the given array
	 *
	 * @param array $parts
	 */
	private function _fromArray (array $parts)
	{
		$this->number   = $parts['number'] ?? '';
		$this->address  = $parts['address'] ?? '';
		$this->city     = $parts['city'] ?? '';
		$this->postcode = $parts['postcode'] ?? '';
		$this->county   = $parts['county'] ?? '';
		$this->state    = $parts['state'] ?? '';
		$this->country  = $parts['country'] ?? '';
	}

	/**
	 * Returns true if the given array is associative
	 *
	 * @param array $arr
	 *
	 * @return bool
	 */
	protected function _isAssoc (array $arr)
	{
		if ([] === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

}
