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
use craft\helpers\App;
use craft\helpers\ConfigHelper;
use ether\simplemap\enums\GeoService;
use ether\simplemap\enums\MapTiles;
use ether\simplemap\services\GeoLocationService;
use ether\simplemap\SimpleMap;
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
	public string $mapTiles = MapTiles::CartoVoyager;

	/** @var string|array The token for the map tile set */
	public string|array $mapToken = '';

	// Properties: Geo-coding
	// -------------------------------------------------------------------------

	/** @var string The geo-coding service to use */
	public string $geoService = GeoService::Nominatim;

	/** @var string|array The token for the geo-coding service */
	public string|array $geoToken = '';

	/**
	 * @var bool Will disable the automatic population of missing field data.
	 *   This can be useful in preventing API spam when importing lots of map
	 *   data.
	 */
	public bool $disablePopulateMissingFieldData = false;

	// Properties: w3w
	// -------------------------------------------------------------------------

	/**
	 * @var bool Will enable what3words integration when set to true
	 */
	public bool $w3wEnabled = false;

	/**
	 * @var string The token (API Key) for what3words
	 */
	public string $w3wToken = '';

	// Properties: Geo-location
	// -------------------------------------------------------------------------

	/** @var string The geo-location service */
	public string $geoLocationService = GeoLocationService::None;

	/** @var string The token for the geo-location service */
	public string $geoLocationToken = '';

	/** @var string|int How long to cache IP look-ups for (set to 0 to disable caching) */
	public string|int $geoLocationCacheDuration = 'P2M';

	/** @var bool Will automatically redirect the user according to $geoLocationRedirectMap when true */
	public bool $geoLocationAutoRedirect = false;

	/**
	 * @var array A key value array where key is the handle of the site to
	 *   redirect, and value is a key value array of user location properties
	 *   and their required matches or an * string to catch all.
	 *
	 * @example [
	 *   'uk'     => [ 'countryCode' => 'uk' ],
	 *   'eu'     => [ 'isEU' => true ],
	 *   'global' => '*',
	 * ]
	 */
	public array $geoLocationRedirectMap = [];

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

	public function isW3WEnabled (): bool
	{
		return $this->w3wEnabled && SimpleMap::v(SimpleMap::EDITION_PRO);
	}

	// Getters
	// =========================================================================

	public function getMapToken (): bool|array|string|null
	{
		return $this->_parseEnv($this->mapToken);
	}

	public function getGeoToken (): bool|array|string|null
	{
		return $this->_parseEnv($this->geoToken);
	}

	public function getW3WToken (): bool|array|string|null
	{
		return $this->_parseEnv($this->w3wToken);
	}

	public function getGeoLocationToken (): bool|array|string|null
	{
		return $this->_parseEnv($this->geoLocationToken);
	}

	// Helpers
	// =========================================================================

	private function _parseEnv ($value): array|bool|string|null
	{
		if (is_string($value))
			return App::parseEnv($value);

		return array_map(function ($v) {
			return App::parseEnv($v);
		}, $value);
	}

}
