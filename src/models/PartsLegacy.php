<?php
/**
 * Maps for Craft CMS 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

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

	// TODO: Add all Google parts

	public static $legacyKeys = [
		'street_address',
		'route',
		'intersection',
		'political',
		'country',
		'administrative_area_level_1',
		'administrative_area_level_2',
		'administrative_area_level_3',
		'administrative_area_level_4',
		'administrative_area_level_5',
		'colloquial_area',
		'locality',
		'sublocality',
		'sublocality_level_1',
		'sublocality_level_5',
		'neighborhood',
		'premise',
		'subpremise',
		'postal_code',
		'natural_feature',
		'airport',
		'park',
		'point_of_interest',
		'floor',
		'establishment',
		'point_of_interest',
		'parking',
		'post_box',
		'postal_town',
		'locality',
		'sublocality',
		'room',
		'street_number',
		'bus_station',
		'train_station',
		'transit_station',
	];

	// Constructor
	// =========================================================================

	public function __construct ($parts = null)
	{
		//
	}

}