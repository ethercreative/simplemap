<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\controllers;

use craft\web\Controller;
use ether\simplemap\enums\GeoService;
use ether\simplemap\enums\MapTiles;
use ether\simplemap\services\GeoLocationService;
use ether\simplemap\SimpleMap;
use yii\web\Response as YiiResponse;

/**
 * Class SettingsController
 *
 * @author  Ether Creative
 * @package ether\simplemap\controllers
 */
class SettingsController extends Controller
{

	public function actionIndex (): YiiResponse
	{
		return $this->renderTemplate(
			'simplemap/settings',
			[
				'isLite'             => SimpleMap::v(SimpleMap::EDITION_LITE),
				'settings'           => SimpleMap::getInstance()->getSettings(),
				'mapTileOptions'     => MapTiles::getSelectOptions(),
				'geoServiceOptions'  => GeoService::getSelectOptions(),
				'geoLocationOptions' => GeoLocationService::getSelectOptions(),
			]
		);
	}

}
