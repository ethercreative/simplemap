<?php

namespace ether\simplemap\fields;

use ether\simplemap\services\MapService;
use yii\validators\Validator;

class MapValidator extends Validator {

	/**
	 * @inheritdoc
	 */
	protected function validateValue ($value)
	{
		if (!is_null($value->lat) && !is_null($value->lng))
			return null;

		if (!is_null($value->address)) {
			$addressToLatLng = MapService::getLatLngFromAddress($value['address']);
			if (is_null($addressToLatLng)) {
				return [
					\Craft::t(
						'simplemap',
						'Missing Lat/Lng or valid address'
					),
					[]
				];
			}

			return null;
		}

		return [
			\Craft::t(
				'simplemap',
				'Missing Lat/Lng'
			),
			[]
		];
	}

}