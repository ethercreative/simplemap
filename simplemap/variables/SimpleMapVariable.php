<?php

namespace Craft;

class SimpleMapVariable {

	public function latLng ($lat, $lng)
	{
		$lat = (float)$lat;
		$lng = (float)$lng;
		
		return compact('lat', 'lng');
	}

	public function apiKey () {
		$apiKey = craft()->config->get('browserApiKey', 'simplemap');

		if ($apiKey)
			return $apiKey;

		return craft()->plugins->getPlugin('simpleMap')->getSettings()->browserApiKey;
	}

	public function getLatLngFromAddress ($address, $country = null)
	{
		return SimpleMapService::getLatLngFromAddress($address, $country);
	}

}