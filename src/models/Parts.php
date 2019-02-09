<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use ether\simplemap\enums\GeoService;

/**
 * Class Parts
 *
 * @author  Ether Creative
 * @package ether\simplemap\models
 */
class Parts
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

	// Methods
	// =========================================================================

	public function __construct (array $parts = [], string $service = '')
	{
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
		}
	}

	// Methods: Static
	// -------------------------------------------------------------------------

	/**
	 * Creates a new Parts from the given array
	 *
	 * @param array $parts
	 *
	 * @return Parts
	 */
	public static function from ($parts)
	{
		$p = new self();

		$p->number   = $parts['number'] ?? '';
		$p->address  = $parts['address'] ?? '';
		$p->city     = $parts['city'] ?? '';
		$p->postcode = $parts['postcode'] ?? '';
		$p->county   = $parts['county'] ?? '';
		$p->state    = $parts['state'] ?? '';
		$p->country  = $parts['country'] ?? '';

		return $p;
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
			$parts,
			function ($a, $part) {
				$key = explode('.', $part['id'])[0];
				$a[$key] = $part['text'];

				return $a;
			},
			[
				'number' => $parts['address'],
				$parts['place_type'][0] => $parts['text'],
			]
		);

		$this->number   = $parts['number'];
		$this->address  = $parts['address'];
		$this->city     = $parts['city'];
		$this->postcode = $parts['postcode'];
		$this->county   = $parts['county'];
		$this->state    = $parts['state'];
		$this->country  = $parts['country'];
	}

	/**
	 * Parse Google parts
	 *
	 * @param $parts
	 */
	private function _google ($parts)
	{
		$parts = array_reduce(
			$parts,
			function ($a, $part) {
				$key = $part['types'][0];
				$a[$key] = $part['long_name'];

				return $a;
			},
			[]
		);

		$this->number = $this->_join([
			$parts['subpremise'],
			$parts['premise'],
			$parts['street_number'],
		]);

		$this->address = $this->_join([
			$parts['route'],
			$parts['neighborhood'],
			$parts['sublocality_level_5'],
			$parts['sublocality_level_4'],
			$parts['sublocality_level_3'],
			$parts['sublocality_level_2'],
			$parts['sublocality_level_1'],
			$parts['sublocality'],
		]);

		$this->city = $this->_join([
			$parts['postal_town'],
			$parts['locality'],
		]);

		$this->postcode = $parts['postal_code'] ?? $parts['postal_code_prefix'];
		$this->county = $parts['administrative_area_level_2'];
		$this->state = $parts['administrative_area_level_1'];
		$this->country = $parts['country'];
	}

	// Methods: Helpers
	// -------------------------------------------------------------------------

	/**
	 * Filters and joins the given array
	 *
	 * @param $parts
	 *
	 * @return string
	 */
	private function _join ($parts)
	{
		return implode(', ', array_filter($parts));
	}

}