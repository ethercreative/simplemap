<?php

namespace Craft;

class SimpleMapVariable {

	public function latLng ($lat, $lng)
	{
		$lat = (float)$lat;
		$lng = (float)$lng;
		
		return compact('lat', 'lng');
	}

}