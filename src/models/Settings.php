<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\maps\models;

use craft\base\Model;
use ether\maps\enums\GeoService;
use ether\maps\enums\MapTiles;

/**
 * Class Settings
 *
 * @author  Ether Creative
 * @package ether\maps\models
 */
class Settings extends Model
{

	// Properties
	// =========================================================================

	/** @deprecated  */
	public $browserApiKey;

	/** @deprecated  */
	public $serverApiKey;

	/** @deprecated  */
	public $apiKey;

	/** @deprecated  */
	public $unrestrictedApiKey;

	// Properties: Map
	// -------------------------------------------------------------------------

	public $mapTiles = MapTiles::Wikimedia;

	public $mapToken = '';

	// Properties: Geo-coding
	// -------------------------------------------------------------------------

	public $geoService = GeoService::Nominatim;

	public $geoToken = '';

}
