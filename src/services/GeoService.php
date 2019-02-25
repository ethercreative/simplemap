<?php
/**
 * Maps for Craft CMS 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\services;

use craft\base\Component;
use craft\helpers\Json;
use ether\simplemap\enums\GeoService as GeoEnum;
use ether\simplemap\models\Settings;
use ether\simplemap\SimpleMap;
use GuzzleHttp\Client;
use yii\db\Exception;

/**
 * Class GeoService
 *
 * @author  Ether Creative
 * @package ether\simplemap\services
 */
class GeoService extends Component
{

	public static function latLngFromAddress ($address, $country = null)
	{
		/** @var Settings $settings */
		$settings = SimpleMap::getInstance()->getSettings();

		switch ($settings->geoService)
		{
			case GeoEnum::AppleMapKit:
				return static::_latLngFromAddress_MapKit(
					$settings->geoToken,
					$address, $country
				);
			case GeoEnum::GoogleMaps:
				return static::_latLngFromAddress_Google(
					$settings->geoToken,
					$address, $country
				);
			case GeoEnum::Mapbox:
				return static::_latLngFromAddress_Mapbox(
					$settings->geoToken,
					$address, $country
				);
			case GeoEnum::Nominatim:
				return static::_latLngFromAddress_Nominatim(
					$address, $country
				);
			default:
				throw new Exception(
					'Unknown geo-coding service: ' . $settings->geoService
				);
		}
	}

	// Private Methods
	// =========================================================================

	// Lat/Lng from Address
	// -------------------------------------------------------------------------

	private static function _latLngFromAddress_MapKit ($token, $address, $country)
	{
		//
	}

	private static function _latLngFromAddress_Google ($token, $address, $country)
	{
		//
	}

	private static function _latLngFromAddress_Mapbox ($token, $address, $country)
	{
		//
	}

	private static function _latLngFromAddress_Nominatim ($address, $country)
	{
		$url = 'https://nominatim.openstreetmap.org/?format=json&limit=1';
		$url .= '&q=' . rawurlencode($address);
		if ($country !== null)
		{
			if (strlen($country) === 2)
				$url .= '&countrycode=' . rawurlencode(strtolower($country));
			else
				$url .= '&country=' . rawurlencode($country);
		}

		$data = (string) static::_client()->get($url)->getBody();
		$data = Json::decodeIfJson($data);

		if (!is_array($data) || empty($data))
			return null;

		return [
			'lat' => $data[0]['lat'],
			'lng' => $data[0]['lon'],
		];
	}

	// Helpers
	// =========================================================================

	private static function _client ()
	{
		static $client;

		if (!$client)
			$client = new Client();

		return $client;
	}

}