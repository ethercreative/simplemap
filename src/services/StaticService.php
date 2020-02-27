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
use craft\helpers\UrlHelper;
use ether\simplemap\enums\MapTiles;
use ether\simplemap\models\Marker;
use ether\simplemap\models\Settings;
use ether\simplemap\models\StaticOptions;
use ether\simplemap\SimpleMap;
use ether\simplemap\utilities\StaticMap;

/**
 * Class StaticService
 *
 * @author  Ether Creative
 * @package ether\simplemap\services
 */
class StaticService extends Component
{

	/**
	 * @param array $options
	 *
	 * @return string|void
	 * @throws \Exception
	 */
	public function generate ($options = [])
	{
		if (SimpleMap::v(SimpleMap::EDITION_LITE))
			return 'Sorry, static maps are a Maps Pro feature!';

		$options = new StaticOptions($options);

		/** @var Settings $settings */
		$settings = SimpleMap::getInstance()->getSettings();

		switch ($settings->mapTiles) {
			case MapTiles::GoogleHybrid:
			case MapTiles::GoogleRoadmap:
			case MapTiles::GoogleTerrain:
				return $this->_generateGoogle($options, $settings);
			case MapTiles::MapKitHybrid:
			case MapTiles::MapKitMutedStandard:
			case MapTiles::MapKitSatellite:
			case MapTiles::MapKitStandard:
				return $this->_generateApple($options, $settings);
			case MapTiles::MapboxDark:
			case MapTiles::MapboxLight:
			case MapTiles::MapboxOutdoors:
			case MapTiles::MapboxStreets:
				return $this->_generateMapbox($options, $settings);
			case MapTiles::HereHybrid:
			case MapTiles::HereNormalDay:
			case MapTiles::HereNormalDayGrey:
			case MapTiles::HereNormalDayTransit:
			case MapTiles::HerePedestrian:
			case MapTiles::HereReduced:
			case MapTiles::HereSatellite:
			case MapTiles::HereTerrain:
				return $this->_generateHere($options, $settings);
			default:
				return $this->_generateDefault($options);
		}
	}

	// Generators
	// =========================================================================

	/**
	 * @param StaticOptions $options
	 * @param Settings      $settings
	 *
	 * @return string
	 * @throws \Exception
	 */
	private function _generateGoogle ($options, $settings)
	{
		$params = [
			'center' => implode(',', $options->getCenter()),
			'zoom' => $options->zoom,
			'size' => $options->getSize(),
			'scale' => $options->scale,
			'language' => Craft::$app->getLocale()->getLanguageID(),
			'region' => $this->_getTld(),
			'key' => GeoService::getToken(
				$settings->getMapToken(),
				$settings->mapTiles
			),
		];

		$markersString = '';
		if (!empty($options->markers))
		{
			$markers = [];

			/** @var Marker $marker */
			foreach ($options->markers as $marker)
			{
				$m = [
					'color:' . str_replace('#', '0x', $marker->color),
				];

				if ($marker->label !== null)
					$m[] = 'label:' . strtoupper($marker->label);

				$m[] = $marker->getLocation();

				$markers[] = implode('|', $m);
			}

			$markersString = '&markers=' . implode('&markers=', $markers);
		}

		switch ($settings->mapTiles)
		{
			case MapTiles::GoogleTerrain:
				$params['maptype'] = 'terrain';
				break;
			case MapTiles::GoogleRoadmap:
				$params['maptype'] = 'roadmap';
				break;
			case MapTiles::GoogleHybrid:
				$params['maptype'] = 'hybrid';
				break;
		}

		return 'https://maps.googleapis.com/maps/api/staticmap?' . http_build_query($params) . $markersString;
	}

	/**
	 * @param StaticOptions $options
	 * @param Settings      $settings
	 *
	 * @return string
	 * @throws \Exception
	 */
	private function _generateApple ($options, $settings)
	{
		$params = [
			'center' => implode(',', $options->getCenter()),
			'z' => $options->zoom,
			'size' => $options->getSize(),
			'scale' => $options->scale,
			'lang' => Craft::$app->getLocale()->getLanguageID(),
			'teamId' => $settings->getMapToken()['teamId'],
			'keyId' => $settings->getMapToken()['keyId'],
		];

		if (!empty($options->markers))
		{
			$params['annotations'] = [];

			foreach ($options->markers as $marker)
			{
				$params['annotations'][] = [
					'color' => str_replace('#', '', $marker->color),
					'glyphText' => $marker->label,
					'point' => $marker->getLocation(true),
				];
			}

			$params['annotations'] = Json::encode($params['annotations']);
		}

		switch ($settings->mapTiles)
		{
			case MapTiles::MapKitStandard:
				$params['type'] = 'standard';
				break;
			case MapTiles::MapKitSatellite:
				$params['type'] = 'satellite';
				break;
			case MapTiles::MapKitMutedStandard:
				$params['type'] = 'mutedStandard';
				break;
			case MapTiles::MapKitHybrid:
				$params['type'] = 'hybrid';
				break;
		}

		$path = '/api/v1/snapshot?' . http_build_query($params);
		openssl_sign($path, $signature, $settings->getMapToken()['privateKey'], OPENSSL_ALGO_SHA256);
		$signature = $this->_encode($signature);

		return 'https://snapshot.apple-mapkit.com' . $path . '&signature=' . $signature;
	}

	/**
	 * @param StaticOptions $options
	 * @param Settings      $settings
	 *
	 * @return string
	 * @throws \Exception
	 */
	private function _generateMapbox ($options, $settings)
	{
		$url = 'https://api.mapbox.com/styles/v1/mapbox/';

		switch ($settings->mapTiles)
		{
			case MapTiles::MapboxStreets:
				$url .= 'streets-v11';
				break;
			case MapTiles::MapboxOutdoors:
				$url .= 'outdoors-v11';
				break;
			case MapTiles::MapboxLight:
				$url .= 'light-v9';
				break;
			case MapTiles::MapboxDark:
				$url .= 'dark-v10';
				break;
		}

		$url .= '/static/';

		if (!empty($options->markers))
		{
			$markers = [];
			$i = 0;

			foreach ($options->markers as $marker)
			{
				$m = 'pin-l-';
				$m .= strtolower($marker->label ?: ++$i);
				$m .= '+' . str_replace('#', '', $marker->color);
				$m .= '(' . implode(',', array_reverse(explode(',', $marker->getLocation(true)))) . ')';

				$markers[] = $m;
			}

			$url .= implode(',', $markers) . '/';
		}

		$center = $options->getCenter();
		$url .= $center['lng'] . ',';
		$url .= $center['lat'] . ',';
		$url .= $options->zoom . ',0,0';
		$url .= '/' . $options->getSize();

		if ($options->scale > 1)
			$url .= '@2x';

		return $url . '?access_token=' . $settings->getMapToken();
	}

	/**
	 * @param StaticOptions $options
	 * @param Settings      $settings
	 *
	 * @return string
	 * @throws \Exception
	 */
	private function _generateHere ($options, $settings)
	{
		$params = [
			'app_id' => $settings->getMapToken()['appId'],
			'app_code' => $settings->getMapToken()['appCode'],
			'nodot' => true,
			'c' => implode(',', $options->getCenter()),
			'z' => $options->zoom,
			'w' => $options->width * $options->scale,
			'h' => $options->height * $options->scale,
		];

		switch ($settings->mapTiles)
		{
			case MapTiles::HereHybrid:
				$params['t'] = 3;
				break;
			case MapTiles::HereNormalDay:
				$params['t'] = 0;
				break;
			case MapTiles::HereNormalDayGrey:
				$params['t'] = 5;
				break;
			case MapTiles::HereNormalDayTransit:
				$params['t'] = 4;
				break;
			case MapTiles::HereReduced:
				$params['t'] = 6;
				break;
			case MapTiles::HerePedestrian:
				$params['t'] = 13;
				break;
			case MapTiles::HereSatellite:
				$params['t'] = 1;
				break;
			case MapTiles::HereTerrain:
				$params['t'] = 2;
				break;
		}

		if (!empty($options->markers))
		{
			$i = 0;

			foreach ($options->markers as $marker)
			{
				$m = [$marker->getLocation(true)];
				$m[] = str_replace('#', '', $marker->color);
				$m[] = StaticMap::getLabelColour($marker->color);
				$m[] = 18;
				$m[] = $marker->label ?: ++$i;

				$params['poix' . ($i - 1)] = implode(';', $m);
			}
		}

		return 'https://image.maps.api.here.com/mia/1.6/mapview?' . http_build_query($params);
	}

	/**
	 * @param StaticOptions $options
	 *
	 * @return string
	 * @throws \Exception
	 */
	private function _generateDefault ($options)
	{
		$center = $options->getCenter();

		return UrlHelper::actionUrl(
			'simplemap/static',
			[
				'lat' => $center['lat'],
				'lng' => $center['lng'],
				'zoom' => $options->zoom,
				'width' => $options->width,
				'height' => $options->height,
				'scale' => $options->scale,
				'markers' => urlencode(implode(';', $options->markers)),
				'csrf' => Craft::$app->getRequest()->getCsrfToken(),
			]
		);
	}

	// Helpers
	// =========================================================================

	private function _getTld ()
	{
		$url = 'http://' . $_SERVER['SERVER_NAME'];

		return explode(".", parse_url($url, PHP_URL_HOST));
	}

	private function _encode ($data)
	{
		$encoded = strtr(base64_encode($data), '+/', '-_');

		return rtrim($encoded, '=');
	}

}
