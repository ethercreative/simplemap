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
use ether\simplemap\enums\MapTiles;
use ether\simplemap\models\Settings;
use ether\simplemap\SimpleMap;
use GuzzleHttp\Client;
use Mapkit\JWT;
use yii\db\Exception;

/**
 * Class GeoService
 *
 * @author  Ether Creative
 * @package ether\simplemap\services
 */
class GeoService extends Component
{

	// Properties
	// =========================================================================

	private static $_countryCodes = [
		'AF',
		'AX',
		'AL',
		'DZ',
		'AS',
		'AD',
		'AO',
		'AI',
		'AQ',
		'AG',
		'AR',
		'AM',
		'AW',
		'AU',
		'AT',
		'AZ',
		'BS',
		'BH',
		'BD',
		'BB',
		'BY',
		'BE',
		'BZ',
		'BJ',
		'BM',
		'BT',
		'BO',
		'BQ',
		'BA',
		'BW',
		'BV',
		'BR',
		'IO',
		'BN',
		'BG',
		'BF',
		'BI',
		'KH',
		'CM',
		'CA',
		'CV',
		'KY',
		'CF',
		'TD',
		'CL',
		'CN',
		'CX',
		'CC',
		'CO',
		'KM',
		'CG',
		'CD',
		'CK',
		'CR',
		'CI',
		'HR',
		'CU',
		'CW',
		'CY',
		'CZ',
		'DK',
		'DJ',
		'DM',
		'DO',
		'EC',
		'EG',
		'SV',
		'GQ',
		'ER',
		'EE',
		'ET',
		'FK',
		'FO',
		'FJ',
		'FI',
		'FR',
		'GF',
		'PF',
		'TF',
		'GA',
		'GM',
		'GE',
		'DE',
		'GH',
		'GI',
		'GR',
		'GL',
		'GD',
		'GP',
		'GU',
		'GT',
		'GG',
		'GN',
		'GW',
		'GY',
		'HT',
		'HM',
		'VA',
		'HN',
		'HK',
		'HU',
		'IS',
		'IN',
		'ID',
		'IR',
		'IQ',
		'IE',
		'IM',
		'IL',
		'IT',
		'JM',
		'JP',
		'JE',
		'JO',
		'KZ',
		'KE',
		'KI',
		'KP',
		'KR',
		'KW',
		'KG',
		'LA',
		'LV',
		'LB',
		'LS',
		'LR',
		'LY',
		'LI',
		'LT',
		'LU',
		'MO',
		'MK',
		'MG',
		'MW',
		'MY',
		'MV',
		'ML',
		'MT',
		'MH',
		'MQ',
		'MR',
		'MU',
		'YT',
		'MX',
		'FM',
		'MD',
		'MC',
		'MN',
		'ME',
		'MS',
		'MA',
		'MZ',
		'MM',
		'NA',
		'NR',
		'NP',
		'NL',
		'NC',
		'NZ',
		'NI',
		'NE',
		'NG',
		'NU',
		'NF',
		'MP',
		'NO',
		'OM',
		'PK',
		'PW',
		'PS',
		'PA',
		'PG',
		'PY',
		'PE',
		'PH',
		'PN',
		'PL',
		'PT',
		'PR',
		'QA',
		'RE',
		'RO',
		'RU',
		'RW',
		'BL',
		'SH',
		'KN',
		'LC',
		'MF',
		'PM',
		'VC',
		'WS',
		'SM',
		'ST',
		'SA',
		'SN',
		'RS',
		'SC',
		'SL',
		'SG',
		'SX',
		'SK',
		'SI',
		'SB',
		'SO',
		'ZA',
		'GS',
		'SS',
		'ES',
		'LK',
		'SD',
		'SR',
		'SJ',
		'SZ',
		'SE',
		'CH',
		'SY',
		'TW',
		'TJ',
		'TZ',
		'TH',
		'TL',
		'TG',
		'TK',
		'TO',
		'TT',
		'TN',
		'TR',
		'TM',
		'TC',
		'TV',
		'UG',
		'UA',
		'AE',
		'GB',
		'US',
		'UM',
		'UY',
		'UZ',
		'VU',
		'VE',
		'VN',
		'VG',
		'VI',
		'WF',
		'EH',
		'YE',
		'ZM',
		'ZW',
	];

	// Methods
	// =========================================================================

	/**
	 * Parses the token based off the given service
	 *
	 * @param array|string $token
	 * @param string       $service
	 *
	 * @return false|string
	 */
	public static function getToken ($token, string $service)
	{
		switch ($service)
		{
			case GeoEnum::AppleMapKit:
			case MapTiles::MapKitStandard:
			case MapTiles::MapKitMutedStandard:
			case MapTiles::MapKitSatellite:
			case MapTiles::MapKitHybrid:
				return JWT::getToken(
					trim($token['privateKey']),
					trim($token['keyId']),
					trim($token['teamId'])
				);
			default:
				return $token;
		}
	}

	/**
	 * Find the lat/lng for the given address
	 *
	 * @param string      $address
	 * @param string|null $country
	 *
	 * @return array|null
	 * @throws Exception
	 */
	public static function latLngFromAddress ($address, $country = null)
	{
		/** @var Settings $settings */
		$settings = SimpleMap::getInstance()->getSettings();
		$token = static::getToken($settings->geoToken, $settings->geoService);

		switch ($settings->geoService)
		{
			case GeoEnum::GoogleMaps:
				return static::_latLngFromAddress_Google(
					$token,
					$address, $country
				);
			case GeoEnum::Mapbox:
				return static::_latLngFromAddress_Mapbox(
					$token,
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

	private static function _latLngFromAddress_Google ($token, $address, $country)
	{
		$url = 'https://maps.googleapis.com/maps/api/geocode/json';
		$url .= '?address=' . rawurlencode($address);
		if ($country !== null)
		{
			if (static::_validateCountryCode($country))
				$url .= '&components=country:' . rawurlencode(strtoupper($country));
			else
				$url .= rawurlencode(', ' . $country);
		}
		$url .= '&key=' . $token;

		$data = (string) static::_client()->get($url)->getBody();
		$data = Json::decodeIfJson($data);

		if (!is_array($data) || empty($data['results']))
			return null;

		return [
			'lat' => $data['results'][0]['geometry']['location']['lat'],
			'lng' => $data['results'][0]['geometry']['location']['lng'],
		];
	}

	private static function _latLngFromAddress_Mapbox ($token, $address, $country)
	{
		$url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/';
		$url .= rawurlencode($address) . '.json?limit=1';
		$url .= '&access_token=' . $token;
		if ($country !== null)
		{
			if (static::_validateCountryCode($country))
				$url .= '&country=' . rawurlencode(strtolower($country));
			else
				$url = str_replace('.json', rawurlencode(', ' . $country), $url);
		}

		$data = (string) static::_client()->get($url)->getBody();
		$data = Json::decodeIfJson($data);

		if (!is_array($data) || empty($data['features']))
			return null;

		return [
			'lat' => $data['features'][0]['center'][1],
			'lng' => $data['features'][0]['center'][0],
		];
	}

	private static function _latLngFromAddress_Nominatim ($address, $country)
	{
		$url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1';
		$url .= '&q=' . rawurlencode($address);
		if ($country !== null)
		{
			if (static::_validateCountryCode($country))
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

	private static function _validateCountryCode (string $code)
	{
		return in_array(strtoupper($code), static::$_countryCodes);
	}

}