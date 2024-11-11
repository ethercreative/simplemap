<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2024 Ether Creative
 */

namespace ether\simplemap\acfadapters;

use craft\base\FieldInterface;
use craft\wpimport\BaseAcfAdapter;
use ether\simplemap\fields\MapField;

/**
 * Class GoogleMap
 *
 * @author  Ether Creative
 * @author  Brandon Kelly
 * @package ether\simplemap\acfadapters
 */
class GoogleMap extends BaseAcfAdapter
{
	public static function type(): string
	{
		return 'google_map';
	}

	public function create(array $data): FieldInterface
	{
		$field = new MapField();
		if ($data['center_lat']) {
			$field->lat = $data['center_lat'];
		}
		if ($data['center_lng']) {
			$field->lng = $data['center_lng'];
		}
		if ($data['zoom']) {
			$field->zoom = $data['zoom'];
		}
		return $field;
	}

	public function normalizeValue(mixed $value, array $data): mixed
	{
		return [
			'address' => $value['address'],
			'lat' => $value['lat'],
			'lng' => $value['lng'],
			'zoom' => $value['zoom'],
			'parts' => [
				'number' => $value['street_number'],
				'address' => $value['street_name'],
				'city' => $value['city'],
				'postcode' => $value['post_code'],
				'state' => $value['state'],
				'country' => $value['country'],
				'administrative_area_level_1' => $value['state'],
				'locality' => $value['city'],
				'postal_code' => $value['post_code'],
				'route' => $value['street_name'],
				'street_number' => $value['street_number'],
			],
		];
	}
}
