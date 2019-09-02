<?php
/**
 * Maps for Craft CMS 3
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\web;

use ether\simplemap\models\Settings;
use ether\simplemap\services\GeoService;
use ether\simplemap\SimpleMap;

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
			$settings->mapToken,
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
		catch (\Exception $e)
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
	 * @throws \Exception
	 */
	public function getImg ($options)
	{
		return SimpleMap::getInstance()->static->generate($options);
	}

}
