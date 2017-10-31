<?php

namespace ether\SimpleMap\fields;

use craft\base\Field;

class MapField extends Field
{

	// Properties
	// =========================================================================

	// Properties: Public
	// -------------------------------------------------------------------------

	/**
	 * @var float - The maps latitude
	 */
	public $lat = 51.272154;

	/**
	 * @var float - The maps longitude
	 */
	public $lng = 0.514951;

	/**
	 * @var int - The maps zoom level
	 */
	public $zoom = 15;

	/**
	 * @var int - The height of the map in pixels
	 */
	public $height = 400;

	/**
	 * @var bool - If true, the map will not be displayed
	 */
	public $hideMap = false;

	/**
	 * @var bool - If true, the lat/lng inputs will not be displayed
	 */
	public $hideLatLng = false;

	/**
	 * @var string|null - The country to restrict the location search to
	 */
	public $countryRestriction;

	/**
	 * @var string|null - The location types to restrict the location search to
	 */
	public $typeRestriction;

	/**
	 * @var float|null - The north east latitude of the map bounds
	 */
	public $boundaryRestrictionNELat;

	/**
	 * @var float|null - The north east longitude of the map bounds
	 */
	public $boundaryRestrictionNELng;

	/**
	 * @var float|null - The south west latitude of the map bounds
	 */
	public $boundaryRestrictionSWLat;

	/**
	 * @var float|null - The south west longitude of the map bounds
	 */
	public $boundaryRestrictionSWLng;

	// Public Functions
	// =========================================================================

	// Public Functions: Static
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 */
	public static function displayName (): string
	{
		return \Craft::t('simplemap', 'Map');
	}

	/**
	 * @inheritdoc
	 */
	public static function hasContentColumn (): bool
	{
		return false;
	}

}