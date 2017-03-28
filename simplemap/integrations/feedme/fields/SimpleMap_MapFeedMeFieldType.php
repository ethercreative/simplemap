<?php
namespace Craft;

class SimpleMap_MapFeedMeFieldType extends BaseFeedMeFieldType
{

	// Templates
	// =========================================================================

	public function getMappingTemplate ()
	{
		return 'simplemap/_integrations/feedme/fields/simplemap_map';
	}


	// Public
	// =========================================================================

	public function prepFieldData (
		$element, $field, $fieldData, $handle, $options
	) {
		// Initialize content array
		$content = array();

		$data = $fieldData['data'];

		foreach ($data as $subfieldHandle => $subfieldData) {
			// Set value to subfield of correct address array
			if (isset($subfieldData['data'])) {
				$content[$subfieldHandle] = $subfieldData['data'];
			}
		}

		// If we have an address, but not a Lat Lng, get the Lat Lng
		if (
			isset($content['address']) &&
			(!isset($content['lat']) || !isset($content['lng']))
		) {
			$latlng = SimpleMapService::getLatLngFromAddress($content['address']);
			$content['lat'] = $latlng['lat'];
			$content['lng'] = $latlng['lng'];
		}

		return $content;
	}

}