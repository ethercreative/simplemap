<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\fields;

use craft\base\ElementInterface;
use craft\base\Field;
use ether\simplemap\enums\GeoService;
use ether\simplemap\models\Settings;
use ether\simplemap\SimpleMap;
use ether\simplemap\web\assets\MapAsset;
use Mapkit\JWT;

/**
 * Class Map
 *
 * @author  Ether Creative
 * @package ether\simplemap\fields
 */
class Map extends Field
{

	// Properties
	// =========================================================================

	/**
	 * @var float - The maps latitude
	 */
	public $lat = 51.272154;

	/**
	 * @var float - The maps longitude
	 */
	public $lng = 0.514951;

	/**
	 * @var int - The maps zoom level
	 */
	public $zoom = 15;

	/**
	 * @var bool - If true, the location search will not be displayed
	 */
	public $hideSearch = false;

	/**
	 * @var bool - If true, the map will not be displayed
	 */
	public $hideMap = false;

	/**
	 * @var bool - If true, the address fields will not be displayed
	 */
	public $hideAddress = false;

	// Methods
	// =========================================================================

	// Methods: Static
	// -------------------------------------------------------------------------

	public static function displayName (): string
	{
		return SimpleMap::t('Map');
	}

	public static function hasContentColumn (): bool
	{
		return false;
	}

	// Methods: Instance
	// -------------------------------------------------------------------------

	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['lat', 'lng', 'zoom'],
			'required',
		];

		$rules[] = [
			['lat'],
			'double',
			'min' => -90,
			'max' => 90,
		];

		$rules[] = [
			['lng'],
			'double',
			'min' => -180,
			'max' => 180,
		];

		return $rules;
	}

	public function getSettingsHtml ()
	{
		return 'TODO: Map field settings';
	}

	/**
	 * @param                       $value
	 * @param ElementInterface|null $element
	 *
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getInputHtml ($value, ElementInterface $element = null): string
	{
		$view = \Craft::$app->getView();

		$view->registerAssetBundle(MapAsset::class);
		$view->registerTranslations('simplemap', [
			'Search for a location',
		]);

		/** @var Settings $settings */
		$settings = SimpleMap::getInstance()->getSettings();

		$opts = [
			'config' => [
				'geoService' => $settings->geoService,
				'geoToken' => $this->_getToken(
					$settings->geoToken,
					$settings->geoService
				),
			],
			'value' => [
				'address' => '',
			],
		];

		if ($settings->geoService === GeoService::GoogleMaps)
		{
			$view->registerJsFile(
				'https://maps.googleapis.com/maps/api/js?libraries=places&key=' .
				$settings->geoToken
			);
		}
		elseif ($settings->geoService === GeoService::AppleMapKit)
		{
			$view->registerJsFile(
				'https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js'
			);
		}

		/** @noinspection PhpComposerExtensionStubsInspection */
		return new \Twig_Markup(
			'<simple-map><script type="application/json">' . json_encode($opts) . '</script></simple-map>',
			'utf-8'
		);
	}

	// Helpers
	// =========================================================================

	private function _getToken ($token, string $service)
	{
		switch ($service)
		{
			case GeoService::AppleMapKit:
				return JWT::getToken(
					trim($token['privateKey']),
					trim($token['keyId']),
					trim($token['teamId'])
				);
			default:
				return $token;
		}
	}

}