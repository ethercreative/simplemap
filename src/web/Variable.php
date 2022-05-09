<?php
/**
 * Maps for Craft CMS 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\web;

use Craft;
use craft\errors\DeprecationException;
use ether\simplemap\models\Settings;
use ether\simplemap\models\UserLocation;
use ether\simplemap\services\GeoService;
use ether\simplemap\SimpleMap;
use Exception;
use yii\base\InvalidConfigException;

/**
 * Class Variable
 *
 * @author  Ether Creative
 * @package ether\simplemap\web
 */
class Variable
{

	/**
	 * Returns the map token
	 *
	 * @return string
	 */
	public function getMapToken (): string
	{
		/** @var Settings $settings */
		$settings = SimpleMap::getInstance()->getSettings();

		return GeoService::getToken(
			$settings->getMapToken(),
			$settings->mapTiles
		);
	}

	/**
	 * Returns the map token
	 *
	 * @deprecated as of 3.4.0
	 * @return string
	 * @throws DeprecationException
	 */
	public function getApiKey (): string
	{
		Craft::$app->getDeprecator()->log(
			'Variable::getApiKey()',
			'ether\simplemap\web\Variable::getApiKey() has been deprecated. Use `getMapToken()` instead.'
		);

		return $this->getMapToken();
	}

	/**
	 * Returns the current users approximate location
	 *
	 * @param string|null $ip - Override the lookup IP
	 *
	 * @return UserLocation|null
	 * @throws Exception
	 */
	public function getUserLocation (string $ip = null): ?UserLocation
	{
		return SimpleMap::getInstance()->geolocation->lookup($ip);
	}

	/**
	 * Converts the given address to lat/lng
	 *
	 * @param string      $address The address to search
	 * @param string|null $country The ISO 3166-1 alpha-2 country code to
	 *                             restrict the search to
	 *
	 * @return array|null
	 */
	public function getLatLngFromAddress (string $address, string $country = null): ?array
	{
		try
		{
			return GeoService::latLngFromAddress($address, $country);
		}
		catch (Exception $e)
		{
			Craft::error($e->getMessage(), 'simplemap');

			return [
				'lat' => '',
				'lng' => '',
			];
		}
	}

	/**
	 * Will return a static map image using the given options.
	 *
	 * @param $options - See StaticOptions for the available options
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getImg ($options): string
	{
		return SimpleMap::getInstance()->static->generate($options);
	}

	/**
	 * Will return a static map image ready for srcset
	 *
	 * @param $options - See StaticOptions for the available options
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getImgSrcSet ($options): string
	{
		$x1 = $this->getImg(array_merge($options, ['scale' => 1]));
		$x2 = $this->getImg(array_merge($options, ['scale' => 2]));

		return $x1 . ' 1x, ' . $x2 . ' 2x';
	}

	/**
	 * Will return markup for a dynamic map embed
	 *
	 * @param $options - See EmbedOptions for the available options
	 *
	 * @return string|void
	 * @throws InvalidConfigException
	 */
	public function getEmbed ($options)
	{
		return SimpleMap::getInstance()->embed->embed($options);
	}

}
