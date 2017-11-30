<?php

namespace ether\simplemap;

use ether\simplemap\services\MapService;
use yii\base\Exception;

class Variable {

	/**
	 * Returns the API key
	 *
	 * @return string
	 */
	public function getApiKey (): string
	{
		return SimpleMap::$plugin->getSettings()->apiKey;
	}

	/**
	 * Converts the given address to lat/lng
	 *
	 * @param string      $address
	 * @param string|null $country
	 *
	 * @return array
	 */
	public function getLatLngFromAddress ($address, $country = null)
	{
		try {
			return MapService::getLatLngFromAddress($address, $country);
		} catch (Exception $e) {
			\Craft::getLogger()->log(
				$e->getMessage(),
				LOG_ERR,
				'simplemap'
			);

			return [
				'lat' => '',
				'lng' => '',
			];
		}
	}

}