<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\fields;

use craft\base\EagerLoadingFieldInterface;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use ether\simplemap\enums\GeoService;
use ether\simplemap\enums\MapTiles;
use ether\simplemap\models\Settings;
use ether\simplemap\SimpleMap;
use ether\simplemap\web\assets\MapAsset;
use ether\simplemap\elements\Map as MapElement;
use Mapkit\JWT;

/**
 * Class Map
 *
 * @author  Ether Creative
 * @package ether\simplemap\fields
 */
class Map extends Field implements EagerLoadingFieldInterface, PreviewableFieldInterface
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

	public static function supportedTranslationMethods (): array
	{
		return [
			self::TRANSLATION_METHOD_NONE,
			self::TRANSLATION_METHOD_SITE,
		];
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

	/**
	 * @param MapElement|null $value
	 * @param ElementInterface|Element|null $element
	 *
	 * @return MapElement
	 */
	public function normalizeValue ($value, ElementInterface $element = null)
	{
		if ($value instanceof MapElement)
			return $value;

		$query = MapElement::find()
			->fieldId($this->id)
			->siteId($element->siteId ?? null);

		if ($element && $element->id)
			$query->ownerId($element->id);

		$map = $query->one();

		if ($map === null)
		{
			if (is_array($value))
				$map = new MapElement($value);
			else
				$map = new MapElement([
					'lat' => $this->lat,
					'lng' => $this->lng,
					'zoom' => $this->zoom,
				]);
		}

		return $map;
	}

	public function getSettingsHtml ()
	{
		return 'TODO: Map field settings';
	}

	/**
	 * @param MapElement $value
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
			'Name / Number',
			'Street Address',
			'Town / City',
			'Postcode',
			'County',
			'State',
			'Country',
		]);

		/** @var Settings $settings */
		$settings = SimpleMap::getInstance()->getSettings();

		$opts = [
			'config' => [
				'name'        => $view->namespaceInputName($this->handle),
				'hideSearch'  => $this->hideSearch,
				'hideMap'     => $this->hideMap,
				'hideAddress' => $this->hideAddress,

				'mapTiles' => $settings->mapTiles,
				'mapToken' => $this->_getToken(
					$settings->mapToken,
					$settings->mapTiles
				),

				'geoService' => $settings->geoService,
				'geoToken'   => $this->_getToken(
					$settings->geoToken,
					$settings->geoService
				),
			],

			'value' => [
				'address' => $value->address,
				'lat'     => $value->lat,
				'lng'     => $value->lng,
				'zoom'    => $value->zoom,
				'parts'   => $value->parts,
			],
		];

		// Map Services
		// ---------------------------------------------------------------------

		if (strpos($settings->mapTiles, 'google') !== false)
		{
			$view->registerJsFile(
				'https://maps.googleapis.com/maps/api/js?libraries=places&key=' .
				$settings->mapToken
			);
		}
		elseif (strpos($settings->mapTiles, 'mapkit') !== false)
		{
			$view->registerJsFile(
				'https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js'
			);
		}

		// Geo Services
		// ---------------------------------------------------------------------

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

	/**
	 * @inheritdoc
	 *
	 * TODO: This
	 *
	 * @param mixed            $value
	 * @param ElementInterface $element
	 *
	 * @return string
	 */
	public function getTableAttributeHtml ($value, ElementInterface $element): string
	{
		return $this->normalizeValue($value, $element)->address;
	}

	/**
	 * @inheritdoc
	 *
	 * TODO: This
	 *
	 * @param array $sourceElements
	 *
	 * @return array|false|void
	 */
	public function getEagerLoadingMap (array $sourceElements)
	{
		// TODO: Implement getEagerLoadingMap() method.
	}

	// Helpers
	// =========================================================================

	/**
	 * Parses the token based off the given service
	 *
	 * @param string|array $token
	 * @param string $service
	 *
	 * @return false|string
	 */
	private function _getToken ($token, string $service)
	{
		switch ($service)
		{
			case GeoService::AppleMapKit:
			case MapTiles::MapKitStandard:
			case MapTiles::MapKitSatellite:
			case MapTiles::MapKitHybrid:
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