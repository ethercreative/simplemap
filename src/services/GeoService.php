<?php
/**
 * Maps for Craft CMS 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use ether\simplemap\enums\GeoService as GeoEnum;
use ether\simplemap\enums\MapTiles;
use ether\simplemap\models\Map;
use ether\simplemap\models\Parts;
use ether\simplemap\models\PartsLegacy;
use ether\simplemap\models\Settings;
use ether\simplemap\SimpleMap;
use GuzzleHttp\Client;
use Mapkit\JWT;
use Exception;

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

	public static $countries = [
		'AF' => 'Afghanistan',
		'AX' => 'Åland Islands',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua and Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia, Plurinational State of',
		'BQ' => 'Bonaire, Sint Eustatius and Saba',
		'BA' => 'Bosnia and Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'BN' => 'Brunei Darussalam',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos (Keeling) Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CG' => 'Congo',
		'CD' => 'Congo, the Democratic Republic of the',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'CI' => 'Côte d\'Ivoire',
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CW' => 'Curaçao',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands (Malvinas)',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard Island and McDonald Islands',
		'VA' => 'Holy See (Vatican City State)',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran, Islamic Republic of',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IM' => 'Isle of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'KP' => 'Korea, Democratic People\'s Republic of',
		'KR' => 'Korea, Republic of',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Lao People\'s Democratic Republic',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macao',
		'MK' => 'Macedonia, the Former Yugoslav Republic of',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia, Federated States of',
		'MD' => 'Moldova, Republic of',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestine, State of',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PN' => 'Pitcairn',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'RE' => 'Réunion',
		'RO' => 'Romania',
		'RU' => 'Russian Federation',
		'RW' => 'Rwanda',
		'BL' => 'Saint Barthélemy',
		'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
		'KN' => 'Saint Kitts and Nevis',
		'LC' => 'Saint Lucia',
		'MF' => 'Saint Martin (French part)',
		'PM' => 'Saint Pierre and Miquelon',
		'VC' => 'Saint Vincent and the Grenadines',
		'WS' => 'Samoa',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome and Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SX' => 'Sint Maarten (Dutch part)',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia and the South Sandwich Islands',
		'SS' => 'South Sudan',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard and Jan Mayen',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syrian Arab Republic',
		'TW' => 'Taiwan, Province of China',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania, United Republic of',
		'TH' => 'Thailand',
		'TL' => 'Timor-Leste',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad and Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks and Caicos Islands',
		'TV' => 'Tuvalu',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'GB' => 'United Kingdom',
		'US' => 'United States',
		'UM' => 'United States Minor Outlying Islands',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VE' => 'Venezuela, Bolivarian Republic of',
		'VN' => 'Viet Nam',
		'VG' => 'Virgin Islands, British',
		'VI' => 'Virgin Islands, U.S.',
		'WF' => 'Wallis and Futuna',
		'EH' => 'Western Sahara',
		'YE' => 'Yemen',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe',
	];

	public static $countriesIso3 = [
		'AD' => 'AND',
		'AE' => 'ARE',
		'AF' => 'AFG',
		'AG' => 'ATG',
		'AI' => 'AIA',
		'AL' => 'ALB',
		'AM' => 'ARM',
		'AO' => 'AGO',
		'AQ' => 'ATA',
		'AR' => 'ARG',
		'AS' => 'ASM',
		'AT' => 'AUT',
		'AU' => 'AUS',
		'AW' => 'ABW',
		'AX' => 'ALA',
		'AZ' => 'AZE',
		'BA' => 'BIH',
		'BB' => 'BRB',
		'BD' => 'BGD',
		'BE' => 'BEL',
		'BF' => 'BFA',
		'BG' => 'BGR',
		'BH' => 'BHR',
		'BI' => 'BDI',
		'BJ' => 'BEN',
		'BL' => 'BLM',
		'BM' => 'BMU',
		'BN' => 'BRN',
		'BO' => 'BOL',
		'BQ' => 'BES',
		'BR' => 'BRA',
		'BS' => 'BHS',
		'BT' => 'BTN',
		'BV' => 'BVT',
		'BW' => 'BWA',
		'BY' => 'BLR',
		'BZ' => 'BLZ',
		'CA' => 'CAN',
		'CC' => 'CCK',
		'CD' => 'COD',
		'CF' => 'CAF',
		'CG' => 'COG',
		'CH' => 'CHE',
		'CI' => 'CIV',
		'CK' => 'COK',
		'CL' => 'CHL',
		'CM' => 'CMR',
		'CN' => 'CHN',
		'CO' => 'COL',
		'CR' => 'CRI',
		'CU' => 'CUB',
		'CV' => 'CPV',
		'CW' => 'CUW',
		'CX' => 'CXR',
		'CY' => 'CYP',
		'CZ' => 'CZE',
		'DE' => 'DEU',
		'DJ' => 'DJI',
		'DK' => 'DNK',
		'DM' => 'DMA',
		'DO' => 'DOM',
		'DZ' => 'DZA',
		'EC' => 'ECU',
		'EE' => 'EST',
		'EG' => 'EGY',
		'EH' => 'ESH',
		'ER' => 'ERI',
		'ES' => 'ESP',
		'ET' => 'ETH',
		'FI' => 'FIN',
		'FJ' => 'FJI',
		'FK' => 'FLK',
		'FM' => 'FSM',
		'FO' => 'FRO',
		'FR' => 'FRA',
		'GA' => 'GAB',
		'GB' => 'GBR',
		'GD' => 'GRD',
		'GE' => 'GEO',
		'GF' => 'GUF',
		'GG' => 'GGY',
		'GH' => 'GHA',
		'GI' => 'GIB',
		'GL' => 'GRL',
		'GM' => 'GMB',
		'GN' => 'GIN',
		'GP' => 'GLP',
		'GQ' => 'GNQ',
		'GR' => 'GRC',
		'GS' => 'SGS',
		'GT' => 'GTM',
		'GU' => 'GUM',
		'GW' => 'GNB',
		'GY' => 'GUY',
		'HK' => 'HKG',
		'HM' => 'HMD',
		'HN' => 'HND',
		'HR' => 'HRV',
		'HT' => 'HTI',
		'HU' => 'HUN',
		'ID' => 'IDN',
		'IE' => 'IRL',
		'IL' => 'ISR',
		'IM' => 'IMN',
		'IN' => 'IND',
		'IO' => 'IOT',
		'IQ' => 'IRQ',
		'IR' => 'IRN',
		'IS' => 'ISL',
		'IT' => 'ITA',
		'JE' => 'JEY',
		'JM' => 'JAM',
		'JO' => 'JOR',
		'JP' => 'JPN',
		'KE' => 'KEN',
		'KG' => 'KGZ',
		'KH' => 'KHM',
		'KI' => 'KIR',
		'KM' => 'COM',
		'KN' => 'KNA',
		'KP' => 'PRK',
		'KR' => 'KOR',
		'KW' => 'KWT',
		'KY' => 'CYM',
		'KZ' => 'KAZ',
		'LA' => 'LAO',
		'LB' => 'LBN',
		'LC' => 'LCA',
		'LI' => 'LIE',
		'LK' => 'LKA',
		'LR' => 'LBR',
		'LS' => 'LSO',
		'LT' => 'LTU',
		'LU' => 'LUX',
		'LV' => 'LVA',
		'LY' => 'LBY',
		'MA' => 'MAR',
		'MC' => 'MCO',
		'MD' => 'MDA',
		'ME' => 'MNE',
		'MF' => 'MAF',
		'MG' => 'MDG',
		'MH' => 'MHL',
		'MK' => 'MKD',
		'ML' => 'MLI',
		'MM' => 'MMR',
		'MN' => 'MNG',
		'MO' => 'MAC',
		'MP' => 'MNP',
		'MQ' => 'MTQ',
		'MR' => 'MRT',
		'MS' => 'MSR',
		'MT' => 'MLT',
		'MU' => 'MUS',
		'MV' => 'MDV',
		'MW' => 'MWI',
		'MX' => 'MEX',
		'MY' => 'MYS',
		'MZ' => 'MOZ',
		'NA' => 'NAM',
		'NC' => 'NCL',
		'NE' => 'NER',
		'NF' => 'NFK',
		'NG' => 'NGA',
		'NI' => 'NIC',
		'NL' => 'NLD',
		'NO' => 'NOR',
		'NP' => 'NPL',
		'NR' => 'NRU',
		'NU' => 'NIU',
		'NZ' => 'NZL',
		'OM' => 'OMN',
		'PA' => 'PAN',
		'PE' => 'PER',
		'PF' => 'PYF',
		'PG' => 'PNG',
		'PH' => 'PHL',
		'PK' => 'PAK',
		'PL' => 'POL',
		'PM' => 'SPM',
		'PN' => 'PCN',
		'PR' => 'PRI',
		'PS' => 'PSE',
		'PT' => 'PRT',
		'PW' => 'PLW',
		'PY' => 'PRY',
		'QA' => 'QAT',
		'RE' => 'REU',
		'RO' => 'ROU',
		'RS' => 'SRB',
		'RU' => 'RUS',
		'RW' => 'RWA',
		'SA' => 'SAU',
		'SB' => 'SLB',
		'SC' => 'SYC',
		'SD' => 'SDN',
		'SE' => 'SWE',
		'SG' => 'SGP',
		'SH' => 'SHN',
		'SI' => 'SVN',
		'SJ' => 'SJM',
		'SK' => 'SVK',
		'SL' => 'SLE',
		'SM' => 'SMR',
		'SN' => 'SEN',
		'SO' => 'SOM',
		'SR' => 'SUR',
		'SS' => 'SSD',
		'ST' => 'STP',
		'SV' => 'SLV',
		'SX' => 'SXM',
		'SY' => 'SYR',
		'SZ' => 'SWZ',
		'TC' => 'TCA',
		'TD' => 'TCD',
		'TF' => 'ATF',
		'TG' => 'TGO',
		'TH' => 'THA',
		'TJ' => 'TJK',
		'TK' => 'TKL',
		'TL' => 'TLS',
		'TM' => 'TKM',
		'TN' => 'TUN',
		'TO' => 'TON',
		'TR' => 'TUR',
		'TT' => 'TTO',
		'TV' => 'TUV',
		'TW' => 'TWN',
		'TZ' => 'TZA',
		'UA' => 'UKR',
		'UG' => 'UGA',
		'UM' => 'UMI',
		'US' => 'USA',
		'UY' => 'URY',
		'UZ' => 'UZB',
		'VA' => 'VAT',
		'VC' => 'VCT',
		'VE' => 'VEN',
		'VG' => 'VGB',
		'VI' => 'VIR',
		'VN' => 'VNM',
		'VU' => 'VUT',
		'WF' => 'WLF',
		'WS' => 'WSM',
		'XK' => 'XKX',
		'YE' => 'YEM',
		'YT' => 'MYT',
		'ZA' => 'ZAF',
		'ZM' => 'ZMB',
		'ZW' => 'ZWE',
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
		$token = static::getToken($settings->getGeoToken(), $settings->geoService);

		switch ($settings->geoService)
		{
			case GeoEnum::Here:
				return static::_latLngFromAddress_Here(
					$token,
					$address, $country
				);
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

	/**
	 * Find an address from the given lat/lng
	 *
	 * @param float $lat
	 * @param float $lng
	 *
	 * @return array|null - Returns the address and associated parts
	 * @throws Exception
	 */
	public static function addressFromLatLng ($lat, $lng)
	{
		/** @var Settings $settings */
		$settings = SimpleMap::getInstance()->getSettings();
		$token    =
			static::getToken($settings->getGeoToken(), $settings->geoService);

		switch ($settings->geoService)
		{
			case GeoEnum::Here:
				return static::_addressFromLatLng_Here(
					$token,
					$lat, $lng
				);
			case GeoEnum::GoogleMaps:
				return static::_addressFromLatLng_Google(
					$token,
					$lat, $lng
				);
			case GeoEnum::Mapbox:
				return static::_addressFromLatLng_Mapbox(
					$token,
					$lat, $lng
				);
			case GeoEnum::Nominatim:
				return static::_addressFromLatLng_Nominatim(
					$lat, $lng
				);
			default:
				throw new Exception(
					'Unknown geo-coding service: ' . $settings->geoService
				);
		}
	}

	/**
	 * Normalize the given distance unit
	 *
	 * @param string $unit
	 *
	 * @return string
	 */
	public static function normalizeDistance ($unit)
	{
		if ($unit === 'miles') $unit = 'mi';
		else if ($unit === 'kilometers') $unit = 'km';
		else if (!in_array($unit, ['mi', 'km'])) $unit = 'km';

		return $unit;
	}

	/**
	 * Will normalize the given location to a lat/lng array
	 *
	 * @param mixed       $location
	 * @param string|null $country
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function normalizeLocation ($location, $country = null)
	{
		if (is_string($location))
			$location = self::latLngFromAddress($location, $country);
		else if ($location instanceof Map)
			$location = ['lat' => $location->lat, 'lng' => $location->lng];
		else if (!is_array($location) || !isset($location['lat'], $location['lng']))
			$location = null;

		return $location;
	}

	// Private Methods
	// =========================================================================

	// Lat/Lng from Address
	// -------------------------------------------------------------------------

	private static function _latLngFromAddress_Here ($token, $address, $country)
	{
		$url = 'https://geocoder.api.here.com/6.2/geocode.json';
		$url .= '?app_id=' . $token['appId'];
		$url .= '&app_code=' . $token['appCode'];
		$url .= '&language=' . Craft::$app->locale->getLanguageID();
		$url .= '&searchtext=' . rawurlencode($address);

		if ($country !== null)
		{
			if (static::_validateCountryCode($country))
				$url .= '&country=' . rawurlencode(strtoupper($country));
			else
				$url .= rawurlencode(', ' . $country);
		}

		$data = (string) static::_client()->get($url)->getBody();
		$data = Json::decodeIfJson($data);

		if (!is_array($data) || empty($data['Response']['View']))
			return null;

		$pos = $data['Response']['View'][0]['Result'][0]['Location']['DisplayPosition'];

		return [
			'lat' => $pos['Latitude'],
			'lng' => $pos['Longitude'],
		];
	}

	private static function _latLngFromAddress_Google ($token, $address, $country)
	{
		$url = 'https://maps.googleapis.com/maps/api/geocode/json';
		$url .= '?address=' . rawurlencode($address);
		$url .= '&language=' . Craft::$app->locale->getLanguageID();
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
		$url .= '&language=' . Craft::$app->locale->getLanguageID();
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
		$url .= '&accept-language=' . Craft::$app->locale->getLanguageID();
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

	// Address from Lat/Lng
	// -------------------------------------------------------------------------

	private static function _addressFromLatLng_Here ($token, $lat, $lng)
	{
		$url = 'https://reverse.geocoder.api.here.com/6.2/reversegeocode.json';
		$url .= '?app_id=' . $token['appId'];
		$url .= '&app_code=' . $token['appCode'];
		$url .= '&language=' . Craft::$app->locale->getLanguageID();
		$url .= '&mode=retrieveAddresses&limit=1&jsonattributes=1';
		$url .= '&prox=' . rawurlencode($lat) . ',' . rawurldecode($lng) . ',1';

		$data = (string) static::_client()->get($url)->getBody();
		$data = Json::decodeIfJson($data);

		if (!is_array($data) || empty($data['Response']['View']))
			return null;

		$pos = $data['Response']['View'][0]['Result'][0]['Location'];

		return [
			'address' => $pos['label'],
			'parts' => new Parts($pos, GeoEnum::Here),
		];
	}

	private static function _addressFromLatLng_Google ($token, $lat, $lng)
	{
		$url = 'https://maps.googleapis.com/maps/api/geocode/json';
		$url .= '?latlng=' . rawurlencode($lat) . ',' . rawurldecode($lng);
		$url .= '&language=' . Craft::$app->locale->getLanguageID();
		$url .= '&key=' . $token;

		$data = (string) static::_client()->get($url)->getBody();
		$data = Json::decodeIfJson($data);

		if (!is_array($data) || empty($data['results']))
			return null;

		$pos = $data['results'][0];

		return [
			'address' => $pos['formatted_address'],
			'parts' => new PartsLegacy($pos['address_components']),
		];
	}

	private static function _addressFromLatLng_Mapbox ($token, $lat, $lng)
	{
		$url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/';
		$url .= rawurlencode($lng) . ',' . rawurldecode($lat) . '.json?limit=1';
		$url .= '&types=address,country,postcode,place,locality,district,neighborhood';
		$url .= '&language=' . Craft::$app->locale->getLanguageID();
		$url .= '&access_token=' . $token;

		$data = (string) static::_client()->get($url)->getBody();
		$data = Json::decodeIfJson($data);

		if (!is_array($data) || empty($data['features']))
			return null;

		$feature = $data['features'][0];

		return [
			'address' => $feature['place_name'],
			'parts' => new Parts($feature, GeoEnum::Mapbox),
		];
	}

	private static function _addressFromLatLng_Nominatim ($lat, $lng)
	{
		$url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&limit=1&addressdetails=1';
		$url .= '&accept-language=' . Craft::$app->locale->getLanguageID();
		$url .= '&lat=' . rawurlencode($lat) . '&lon=' . rawurldecode($lng);

		$data = (string) static::_client()->get($url)->getBody();
		$data = Json::decodeIfJson($data);

		if (!is_array($data) || empty($data))
			return null;

		return [
			'address' => $data['display_name'],
			'parts' => new Parts(
				array_merge(
					$data['address'],
					['type' => $data['type']]
				),
				GeoEnum::Nominatim
			),
		];
	}

	// Helpers
	// =========================================================================

	private static function _client ()
	{
		static $client;

		if (!$client)
			$client = Craft::createGuzzleClient();

		return $client;
	}

	private static function _validateCountryCode (string $code)
	{
		return in_array(strtoupper($code), array_keys(static::$countries));
	}

}
