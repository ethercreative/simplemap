<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use Craft;
use craft\base\Model;
use craft\helpers\ConfigHelper;
use ether\simplemap\enums\GeoService;
use ether\simplemap\enums\MapTiles;
use ether\simplemap\services\GeoLocationService;
use Exception;

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

	/** @deprecated */
	public $browserApiKey, $serverApiKey, $apiKey, $unrestrictedApiKey;

	// Properties: Map
	// -------------------------------------------------------------------------

	/** @var string The map tile set to use */
	public $mapTiles = MapTiles::Wikimedia;

	/** @var string|array The token for the map tile set */
	public $mapToken = '';

	// Properties: Geo-coding
	// -------------------------------------------------------------------------

	/** @var string The geo-coding service to use */
	public $geoService = GeoService::Nominatim;

	/** @var string|array The token for the geo-coding service */
	public $geoToken = '';

	// Properties: Geo-location
	// -------------------------------------------------------------------------

	/** @var string The geo-location service */
	public $geoLocationService = GeoLocationService::None;

	/** @var string The token for the geo-location service */
	public $geoLocationToken = '';

	/** @var string|int How long to cache IP look-ups for (set to 0 to disable caching) */
	public $geoLocationCacheDuration = 'P2M';

	/** @var bool Will automatically redirect the user according to $geoLocationRedirectMap when true */
	public $geoLocationAutoRedirect = false;

	/**
	 * @var array A key value array where key is the handle of the site to
	 *   redirect, and value is a key value array of user location properties
	 *   and their required matches or an * string to catch all.
	 *
	 * @example [
	 *   'uk'     => [ 'country' => 'uk' ],
	 *   'eu'     => [ 'isEU' => true ],
	 *   'global' => '*',
	 * ]
	 */
	public $geoLocationRedirectMap = [];

	// Methods
	// =========================================================================

	public function __construct ($config = [])
	{
		parent::__construct($config);

		try {
			$this->geoLocationCacheDuration = ConfigHelper::durationInSeconds(
				$this->geoLocationCacheDuration
			);
		} catch (Exception $e) {
			Craft::error($e->getMessage());
		}
	}

}
