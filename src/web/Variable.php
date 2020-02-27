<?php
/**
 * Maps for Craft CMS 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\web;

use ether\simplemap\models\Settings;
use ether\simplemap\models\UserLocation;
use ether\simplemap\services\GeoService;
use ether\simplemap\SimpleMap;
use Exception;

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
	public function getMapToken ()
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
	 * @throws \craft\errors\DeprecationException
	 */
	public function getApiKey ()
	{
		\Craft::$app->getDeprecator()->log(
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
	public function getUserLocation ($ip = null)
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
	public function getLatLngFromAddress ($address, $country = null)
	{
		try
		{
			return GeoService::latLngFromAddress($address, $country);
		}
		catch (Exception $e)
		{
			\Craft::error($e->getMessage(), 'simplemap');

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
	public function getImg ($options)
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
	public function getImgSrcSet ($options)
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
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getEmbed ($options)
	{
		return SimpleMap::getInstance()->embed->embed($options);
	}

}
