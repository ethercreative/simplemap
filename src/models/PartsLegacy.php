<?php
/**
 * Maps for Craft CMS 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use Craft;
use craft\helpers\Json;
use ether\simplemap\enums\GeoService;

/**
 * Class PartsLegacy
 *
 * @author  Ether Creative
 * @package ether\simplemap\models
 */
class PartsLegacy extends Parts
{

	// Properties
	// =========================================================================

	// Properties: Static
	// -------------------------------------------------------------------------

	public static $legacyKeys = [
		'administrative_area_level_1',
		'administrative_area_level_2',
		'administrative_area_level_3',
		'administrative_area_level_4',
		'administrative_area_level_5',
		'airport',
		'bus_station',
		'colloquial_area',
		'country',
		'establishment',
		'floor',
		'intersection',
		'locality',
		'natural_feature',
		'neighborhood',
		'park',
		'parking',
		'point_of_interest',
		'political',
		'post_box',
		'postal_code',
		'postal_code_prefix',
		'postal_code_suffix',
		'postal_town',
		'premise',
		'room',
		'route',
		'street_address',
		'street_number',
		'sublocality',
		'sublocality_level_1',
		'sublocality_level_2',
		'sublocality_level_3',
		'sublocality_level_4',
		'sublocality_level_5',
		'subpremise',
		'train_station',
		'transit_station',
	];

	// Properties: Instance
	// -------------------------------------------------------------------------

	public $administrative_area_level_1;
	public $administrative_area_level_2;
	public $administrative_area_level_3;
	public $administrative_area_level_4;
	public $administrative_area_level_5;
	public $airport;
	public $bus_station;
	public $colloquial_area;
	public $establishment;
	public $floor;
	public $intersection;
	public $locality;
	public $natural_feature;
	public $neighborhood;
	public $park;
	public $parking;
	public $point_of_interest;
	public $political;
	public $post_box;
	public $postal_code;
	public $postal_code_prefix;
	public $postal_code_suffix;
	public $postal_town;
	public $premise;
	public $room;
	public $route;
	public $street_address;
	public $street_number;
	public $sublocality;
	public $sublocality_level_1;
	public $sublocality_level_2;
	public $sublocality_level_3;
	public $sublocality_level_4;
	public $sublocality_level_5;
	public $subpremise;
	public $train_station;
	public $transit_station;

	public $administrative_area_level_1_short;
	public $administrative_area_level_2_short;
	public $administrative_area_level_3_short;
	public $administrative_area_level_4_short;
	public $administrative_area_level_5_short;
	public $airport_short;
	public $bus_station_short;
	public $colloquial_area_short;
	public $establishment_short;
	public $floor_short;
	public $intersection_short;
	public $locality_short;
	public $natural_feature_short;
	public $neighborhood_short;
	public $park_short;
	public $parking_short;
	public $point_of_interest_short;
	public $political_short;
	public $post_box_short;
	public $postal_code_short;
	public $postal_code_prefix_short;
	public $postal_code_suffix_short;
	public $postal_town_short;
	public $premise_short;
	public $room_short;
	public $route_short;
	public $street_address_short;
	public $street_number_short;
	public $sublocality_short;
	public $sublocality_level_1_short;
	public $sublocality_level_2_short;
	public $sublocality_level_3_short;
	public $sublocality_level_4_short;
	public $sublocality_level_5_short;
	public $subpremise_short;
	public $train_station_short;
	public $transit_station_short;

	// Constructor
	// =========================================================================

	public function __construct ($parts = null)
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

		\Yii::configure($this, $parts);

		parent::__construct($parts, GeoService::GoogleMaps);
	}

	public function __set ($name, $value)
	{
		// Prevent setting any new parameters that we don't support
		if (!$this->hasProperty($name))
		{
			$name = Json::encode($name);
			$value = Json::encode($value);

			Craft::info(
				'Attempted to set unsupported legacy part: "' . $name . '" to value "' . $value . '"',
				'simplemap'
			);
			return;
		}

		parent::__set($name, $value);
	}

}
