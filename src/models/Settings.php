<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use craft\base\Model;
use ether\simplemap\enums\GeoService;
use ether\simplemap\enums\MapTiles;

/**
 * Class Settings
 *
 * @author  Ether Creative
 * @package ether\simplemap\models
 */
class Settings extends Model
{

	// Properties
	// =========================================================================

	/** @deprecated  */
	public $browserApiKey;

	/** @deprecated  */
	public $serverApiKey;

	// Properties: Map
	// -------------------------------------------------------------------------

	public $mapTiles = MapTiles::Wikimedia;

	public $mapToken = '';

	// Properties: Geo-coding
	// -------------------------------------------------------------------------

	public $geoService = GeoService::Nominatim;

	public $geoToken = '';

}