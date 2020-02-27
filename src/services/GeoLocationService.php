<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use ether\simplemap\jobs\MaxMindDBDownloadJob;
use ether\simplemap\models\Settings;
use ether\simplemap\models\UserLocation;
use ether\simplemap\SimpleMap;
use Exception;
use GeoIp2\Database\Reader;
use GeoIp2\WebService\Client;

/**
 * Class GeoLocationService
 *
 * @author  Ether Creative
 * @package ether\simplemap\services
 */
class GeoLocationService extends Component
{

	// Consts
	// =========================================================================

	const DB_STORAGE = '@runtime/maps/db';

	const None = 'none';
	const IpStack = 'ipstack';
	const MaxMindLite = 'maxmind-lite';
	const MaxMind = 'maxmind';

	// Methods
	// =========================================================================

	/**
	 * Lookup the location of the current users IP (or passed IP)
	 *
	 * @param string|null $ip
	 *
	 * @return UserLocation|null
	 * @throws Exception
	 */
	public function lookup ($ip = null)
	{
		if (SimpleMap::v(SimpleMap::EDITION_LITE))
			throw new Exception('Sorry, user geolocation is a Maps Pro feature!');

		if (!$ip)
			$ip = Craft::$app->getRequest()->getUserIP();

		if (!self::_isValidIp($ip))
		{
			Craft::error('Invalid or not allowed IP address: "' . $ip . '"', 'maps');

			return null;
		}

		/** @var Settings $settings */
		$settings = SimpleMap::getInstance()->getSettings();

		if ($cached = $this->_getUserLocationFromCache($ip, $settings))
			return $cached;

		$userLocation = null;

		switch ($settings->geoLocationService)
		{
			case self::IpStack:
				$userLocation = $this->_lookup_IpStack($settings->getGeoLocationToken(), $ip);
				break;
			case self::MaxMind:
				$userLocation = $this->_lookup_MaxMind($settings->getGeoLocationToken(), $ip);
				break;
			case self::MaxMindLite:
				$userLocation = $this->_lookup_MaxMindLite($ip);
				break;
			case self::None:
			default:
				$userLocation = null;
		}

		if ($userLocation)
			$this->_cacheUserLocation($userLocation, $settings);

		return $userLocation;
	}

	/**
	 * @throws Exception
	 */
	public function redirect ()
	{
		$settings = SimpleMap::getInstance()->getSettings();
		$siteHandle = null;
		$location = $this->lookup();

		if (!$location || empty($settings->geoLocationAutoRedirect))
			return;

		foreach ($settings->geoLocationRedirectMap as $handle => $props)
		{
			if ($props === '*')
			{
				$siteHandle = $handle;
				continue;
			}

			if (self::_validateProps($location, $props))
			{
				$siteHandle = $handle;
				break;
			}
		}

		if ($siteHandle === null)
			return;

		$site = Craft::$app->getSites()->getSiteByHandle($siteHandle);

		if ($site->id === Craft::$app->getSites()->getCurrentSite()->id)
			return;

		if (!$site)
		{
			Craft::error('Unable to find site with handle "' . $siteHandle . '"', 'simplemap');
			return;
		}

		if (!$site->hasUrls)
		{
			Craft::error('Selected site (' . $siteHandle . ') doesn\'t have URLs!', 'simplemap');
			return;
		}

		$currentBaseUrl = Craft::$app->getSites()->getCurrentSite()->getBaseUrl();
		$currentUrl = str_replace(
			$currentBaseUrl,
			'',
			Craft::$app->getRequest()->getAbsoluteUrl()
		);

		if (strpos($currentUrl, '://') !== false)
			$currentUrl = str_replace(
				Craft::$app->getRequest()->getBaseUrl(),
				'',
				$currentUrl
			);

		$url = rtrim($site->getBaseUrl(), '/') . '/' . $currentUrl;

		Craft::$app->getResponse()->redirect($url);
	}

	// Public Helpers
	// =========================================================================

	public static function getSelectOptions ()
	{
		return [
			self::None => SimpleMap::t('None'),
			self::IpStack => SimpleMap::t('ipstack'),
			self::MaxMindLite => SimpleMap::t('MaxMind (Lite, ~60MB download)'),
			self::MaxMind => SimpleMap::t('MaxMind'),
		];
	}

	// MaxMind DB
	// -------------------------------------------------------------------------

	/**
	 * Check if the database file exists
	 *
	 * @param string $filename
	 *
	 * @return bool
	 */
	public static function dbExists ($filename = 'default.mmdb')
	{
		return file_exists(
			Craft::getAlias(self::DB_STORAGE . DIRECTORY_SEPARATOR . $filename)
		);
	}

	/**
	 * Should we update the database (is it older than 1 week?)
	 *
	 * @param string $filename
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function dbShouldUpdate ($filename = 'default.mmdb')
	{
		$updated = filemtime(
			Craft::getAlias(self::DB_STORAGE . DIRECTORY_SEPARATOR . $filename)
		);

		if ($updated === false) return false;
		return $updated < (new \DateTime())->modify('-7 days')->getTimestamp();
	}

	/**
	 * Start the MaxMind DB download job
	 */
	public static function dbQueueDownload ()
	{
		if (Craft::$app->getCache()->get('maps_db_updating'))
			return;

		Craft::$app->getCache()->set('maps_db_updating', true);
		Craft::$app->getQueue()->push(new MaxMindDBDownloadJob());
	}

	public static function purgeDb ($filename = 'default.mmdb')
	{
		$file = Craft::getAlias(self::DB_STORAGE . DIRECTORY_SEPARATOR . $filename);

		if (file_exists($file))
			unlink($file);
	}

	// Private Helpers
	// =========================================================================

	// Caching
	// -------------------------------------------------------------------------

	/**
	 * @param string   $ip
	 * @param Settings $settings
	 *
	 * @return UserLocation|false
	 */
	private function _getUserLocationFromCache ($ip, Settings $settings)
	{
		if (!$settings->geoLocationCacheDuration)
			return false;

		return Craft::$app->getCache()->get(
			'maps_ip_' . $ip
		);
	}

	/**
	 * @param UserLocation $userLocation
	 * @param Settings     $settings
	 *
	 * @return bool
	 */
	private function _cacheUserLocation (UserLocation $userLocation, Settings $settings)
	{
		if (!$settings->geoLocationCacheDuration)
			return true;

		return Craft::$app->getCache()->set(
			'maps_ip_' . $userLocation->ip,
			$userLocation,
			$settings->geoLocationCacheDuration
		);
	}

	// Lookup Services
	// -------------------------------------------------------------------------

	private function _lookup_IpStack ($token, $ip)
	{
		$url = 'http://api.ipstack.com/' . $ip;
		$url .= '?access_key=' . $token;
		$url .= '&language=' . Craft::$app->getLocale()->getLanguageID();

		$data = self::_client()->get($url)->getBody();
		$data = Json::decodeIfJson($data);

		if (array_key_exists('success', $data) && $data['success'] === false)
		{
			Craft::error($data['error']['info'], 'maps');

			return null;
		}

		$parts = [
			'city'     => $data['city'],
			'postcode' => $data['zip'],
			'state'    => $data['region_name'],
			'country'  => $data['country_name'],
		];

		return new UserLocation([
			'ip'          => $ip,
			'lat'         => $data['latitude'],
			'lng'         => $data['longitude'],
			'address'     => implode(', ', array_filter($parts)),
			'countryCode' => $data['country_code'],
			'isEU'        => $data['location']['is_eu'],
			'parts'       => $parts,
		]);
	}

	private function _lookup_MaxMind ($token, $ip)
	{
		$client = new Client(
			$token['accountId'],
			$token['licenseKey'],
			[
				Craft::$app->getLocale()->getLanguageID(),
				'en'
			]
		);

		$record = null;

		try {
			$record = $client->city($ip);
		} catch (Exception $e) {
			Craft::error($e->getMessage(), 'maps');

			return null;
		}

		return self::_populateMaxMind($ip, $record);
	}

	/**
	 * @param $ip
	 *
	 * @return UserLocation|null
	 * @throws Exception
	 */
	private function _lookup_MaxMindLite ($ip)
	{
		if (!self::dbExists())
		{
			self::dbQueueDownload();

			throw new Exception('No MaxMind database exists, starting download...');
		}

		if (self::dbShouldUpdate())
			self::dbQueueDownload();

		try
		{
			$reader = new Reader(
				Craft::getAlias(
					self::DB_STORAGE . DIRECTORY_SEPARATOR . 'default.mmdb'
				)
			);
			$record = $reader->city($ip);
		} catch (Exception $e)
		{
			Craft::dd($e);
			Craft::error($e->getMessage(), 'maps');

			return null;
		}

		return self::_populateMaxMind($ip, $record);
	}

	// Misc
	// -------------------------------------------------------------------------

	private static function _client ()
	{
		static $client;

		if (!$client)
			$client = Craft::createGuzzleClient();

		return $client;
	}

	/**
	 * Ensure IP is valid and not private or reserved
	 *
	 * @param string $ip
	 *
	 * @return mixed
	 */
	private static function _isValidIp ($ip)
	{
		return filter_var(
			$ip,
			FILTER_VALIDATE_IP,
			FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
		);
	}

	/**
	 * @param $ip
	 * @param $record
	 *
	 * @return UserLocation
	 */
	private static function _populateMaxMind ($ip, $record)
	{
		$parts = [
			'city'     => $record->city->name,
			'postcode' => $record->postal->code,
			'state'    => $record->mostSpecificSubdivision->name,
			'country'  => $record->country->name,
		];

		return new UserLocation([
			'ip'          => $ip,
			'lat'         => $record->location->latitude,
			'lng'         => $record->location->longitude,
			'address'     => implode(', ', array_filter($parts)),
			'countryCode' => $record->country->isoCode,
			'isEU'        => $record->country->isInEuropeanUnion,
			'parts'       => $parts,
		]);
	}

	/**
	 * @param UserLocation $location
	 * @param              $props
	 *
	 * @return bool
	 */
	private static function _validateProps (UserLocation $location, $props)
	{
		foreach ($props as $key => $value)
		{
			if (is_array($value))
			{
				foreach ($value as $item)
					if ($location->$key === $item)
						continue;

				return false;
			}

			if (is_callable($value))
				return $value($location->$key);

			if ($location->$key !== $value)
				return false;
		}

		return true;
	}

}
