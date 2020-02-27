<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\fields;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Json;
use ether\simplemap\enums\GeoService as GeoEnum;
use ether\simplemap\integrations\graphql\MapType;
use ether\simplemap\models\Settings;
use ether\simplemap\services\GeoService;
use ether\simplemap\SimpleMap;
use ether\simplemap\models\Map;
use ether\simplemap\web\assets\MapAsset;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\InvalidConfigException;
use yii\db\Schema;

/**
 * Class Map
 *
 * @author  Ether Creative
 * @package ether\simplemap\fields
 */
class MapField extends Field implements PreviewableFieldInterface
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
	 * @var float - The maps min zoom level (how far OUT it can be zoomed)
	 */
	public $minZoom = 3;

	/**
	 * @var float - The maps max zoom level (how far IN it can be zoomed)
	 */
	public $maxZoom = 18;

	/**
	 * @var string - The preferred country when searching
	 */
	public $country = null;

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

	/**
	 * @var bool - If true, show the lat/lng fields
	 */
	public $showLatLng = false;

	/**
	 * @var bool - If true, will show a button to centre the map on the users
	 *   current location.
	 */
	public $showCurrentLocation = false;

	/**
	 * @var string - The size of the field
	 *   (can be either "normal", "mini")
	 */
	public $size = 'normal';

	/**
	 * @var bool - Will show the what3words overlay when true
	 */
	public $showW3WGrid = false;

	/**
	 * @var bool - Show the what3words field on the map field
	 */
	public $showW3WField = false;

	/**
	 * @deprecated
	 */
	public $hideLatLng;
	/**
	 * @deprecated
	 */
	public $height;
	/**
	 * @deprecated
	 */
	public $countryRestriction;
	/**
	 * @deprecated
	 */
	public $typeRestriction;
	/**
	 * @deprecated
	 */
	public $boundaryRestrictionNELat;
	/**
	 * @deprecated
	 */
	public $boundaryRestrictionNELng;
	/**
	 * @deprecated
	 */
	public $boundaryRestrictionSWLat;
	/**
	 * @deprecated
	 */
	public $boundaryRestrictionSWLng;
	/**
	 * @deprecated
	 */
	public $boundary = '""';

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
		return true;
	}

	public function getContentColumnType (): string
	{
		return Schema::TYPE_TEXT;
	}

	public static function supportedTranslationMethods (): array
	{
		return [
			self::TRANSLATION_METHOD_NONE,
			self::TRANSLATION_METHOD_SITE,
			self::TRANSLATION_METHOD_SITE_GROUP,
			self::TRANSLATION_METHOD_LANGUAGE,
			self::TRANSLATION_METHOD_CUSTOM,
		];
	}

	// Methods: Instance
	// -------------------------------------------------------------------------

	public function setCountryRestriction ($value)
	{
		$this->country = $value;
	}

	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['zoom', 'minZoom', 'maxZoom'],
			'required',
		];

		$rules[] = [
			['minZoom', 'maxZoom'],
			'double',
			'min' => 0,
			'max' => 18,
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
	 * @param Map|array|string|null         $value
	 * @param ElementInterface|Element|null $element
	 *
	 * @return Map
	 * @throws \Exception
	 */
	public function normalizeValue ($value, ElementInterface $element = null)
	{
		if (is_string($value))
			$value = Json::decodeIfJson($value);

		if ($value instanceof Map)
			$map = $value;
		elseif (is_array($value))
			$map = new Map($value);
		else
			$map = new Map([
				'lat'  => null,
				'lng'  => null,
				'zoom' => $this->zoom,
			]);

		SimpleMap::getInstance()->map->populateMissingData($map, $this);

		$map->fieldId = $this->id;

		if ($element)
		{
			$map->ownerId     = $element->id;
			$map->ownerSiteId = $element->siteId;

			$handle = $this->handle;
			$element->setFieldValue($handle, $map);
		}

		return $map;
	}

	/**
	 * @return string|Markup|null
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 * @throws InvalidConfigException
	 * @throws \yii\base\Exception
	 */
	public function getSettingsHtml ()
	{
		$value = new Map();

		$value->lat  = $this->lat;
		$value->lng  = $this->lng;
		$value->zoom = $this->zoom;

		$originalHandle              = $this->handle;
		$originalCountry             = $this->country;
		$originalHideSearch          = $this->hideSearch;
		$originalHideMap             = $this->hideMap;
		$originalHideAddress         = $this->hideAddress;
		$originalShowCurrentLocation = $this->showCurrentLocation;
		$originalSize                = $this->size;
		$originalShowW3WGrid         = $this->showW3WGrid;
		$originalShowW3WField        = $this->showW3WField;

		$this->handle              = '__settings__';
		$this->country             = null;
		$this->hideSearch          = false;
		$this->hideMap             = false;
		$this->hideAddress         = true;
		$this->showCurrentLocation = true;
		$this->size                = 'normal';
		$this->showW3WGrid         = false;
		$this->showW3WField        = false;

		$mapField = new Markup(
			$this->_renderMap($value, true),
			'utf-8'
		);

		$this->handle              = $originalHandle;
		$this->country             = $originalCountry;
		$this->hideSearch          = $originalHideSearch;
		$this->hideMap             = $originalHideMap;
		$this->hideAddress         = $originalHideAddress;
		$this->showCurrentLocation = $originalShowCurrentLocation;
		$this->size                = $originalSize;
		$this->showW3WGrid         = $originalShowW3WGrid;
		$this->showW3WField        = $originalShowW3WField;

		$view = Craft::$app->getView();

		$countries = array_merge([
			'*' => SimpleMap::t('All Countries'),
		], GeoService::$countries);

		return $view->renderTemplate('simplemap/field-settings', [
			'map' => $mapField,
			'field' => $this,
			'countries' => $countries,
			'settings' => SimpleMap::getInstance()->getSettings(),
		]);
	}

	/**
	 * @param Map $value
	 * @param ElementInterface|Element|null $element
	 *
	 * @return string
	 * @throws InvalidConfigException
	 */
	public function getInputHtml ($value = null, ElementInterface $element = null): string
	{
		if ($element !== null && $element->hasEagerLoadedElements($this->handle))
			$value = $element->getEagerLoadedElements($this->handle);

		return new Markup(
			$this->_renderMap($value ?: new Map()),
			'utf-8'
		);
	}

	/**
	 * @inheritdoc
	 *
	 * @param mixed            $value
	 * @param ElementInterface $element
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getTableAttributeHtml ($value, ElementInterface $element): string
	{
		return $this->normalizeValue($value, $element)->address;
	}

	/**
	 * @param ElementQueryInterface $query
	 * @param                       $value
	 *
	 * @return bool|false|null
	 * @throws \Exception
	 */
	public function modifyElementsQuery (ElementQueryInterface $query, $value)
	{
		if (!SimpleMap::getInstance())
			return null;

		SimpleMap::getInstance()->map->modifyElementsQuery($query, $value, $this);

		return null;
	}

	/**
	 * @inheritdoc
	 * @throws \Exception
	 */
	public function isValueEmpty ($value, ElementInterface $element): bool
	{
		return $this->normalizeValue($value)->isValueEmpty();
	}

	// GraphQl
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 */
	public function getContentGqlType ()
	{
		return MapType::getType();
	}

	// Methods: Events
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 */
	public function beforeSave (bool $isNew): bool
	{
		$this->lat  = (float) $this->lat;
		$this->lng  = (float) $this->lng;
		$this->zoom = (int) $this->zoom;

		if ($this->country === '*')
			$this->country = null;

		return parent::beforeSave($isNew);
	}

	/**
	 * @param ElementInterface|Element $element
	 * @param bool             $isNew
	 *
	 * @return bool
	 */
	public function beforeElementSave (ElementInterface $element, bool $isNew): bool
	{
		if (!SimpleMap::getInstance()->map->validateField($this, $element))
			return false;

		return parent::beforeElementSave($element, $isNew);
	}

	/**
	 * @param ElementInterface|Element $element
	 * @param bool             $isNew
	 *
	 * @throws Throwable
	 */
	public function afterElementSave (ElementInterface $element, bool $isNew)
	{
		SimpleMap::getInstance()->map->saveField($this, $element);

		parent::afterElementSave($element, $isNew);
	}

	// Helpers
	// =========================================================================

	/**
	 * Renders the map input
	 *
	 * @param Map $value
	 * @param bool $isSettings
	 *
	 * @return string
	 * @throws InvalidConfigException
	 */
	private function _renderMap ($value, $isSettings = false)
	{
		$view = Craft::$app->getView();

		$containerId = 'map-' . $this->id . '-container';
		$vueContainerId = $view->namespaceInputId($containerId);
		$view->registerJsFile('https://polyfill.io/v3/polyfill.min.js?flags=gated&features=default%2CIntersectionObserver%2CIntersectionObserverEntry');
		$view->registerAssetBundle(MapAsset::class);
		$view->registerJs('new Vue({ el: \'#' . $vueContainerId . '\' });');
		$view->registerTranslations('simplemap', [
			'Search for a location',
			'Clear address',
			'Name / Number',
			'Street Address',
			'Town / City',
			'Postcode',
			'County',
			'State',
			'Country',
			'Latitude',
			'Longitude',
			'Zoom In',
			'Zoom Out',
			'Center on Marker',
			'Current Location',
			'No address selected',
			'what3words',
		]);

		/** @var Settings $settings */
		$settings = SimpleMap::getInstance()->getSettings();

		$country = $this->country;
		// Convert ISO2 to ISO3 for Here autocomplete
		if ($country && $settings->geoService === GeoEnum::Here)
			$country = GeoService::$countriesIso3[$country];

		$opts = [
			'config' => [
				'isSettings' => $isSettings,

				'name'                => $view->namespaceInputName($this->handle),
				'country'             => $country,
				'hideSearch'          => (bool) $this->hideSearch,
				'hideMap'             => (bool) $this->hideMap,
				'hideAddress'         => (bool) $this->hideAddress,
				'showLatLng'          => (bool) $this->showLatLng,
				'showCurrentLocation' => (bool) $this->showCurrentLocation,
				'minZoom'             => $isSettings ? 0 : (float) $this->minZoom,
				'maxZoom'             => $isSettings ? 18 : (float) $this->maxZoom,
				'size'                => $this->size,

				'mapTiles' => $settings->mapTiles,
				'mapToken' => GeoService::getToken(
					$settings->getMapToken(),
					$settings->mapTiles
				),

				'geoService' => $settings->geoService,
				'geoToken'   => GeoService::getToken(
					$settings->getGeoToken(),
					$settings->geoService
				),

				'w3wEnabled'   => $settings->isW3WEnabled(),
				'showW3WGrid'  => (bool) $this->showW3WGrid,
				'showW3WField' => (bool) $this->showW3WField,

				'locale' => Craft::$app->locale->getLanguageID(),
			],

			'value' => [
				'address'    => $value->address,
				'lat'        => self::_parseFloat($value->lat),
				'lng'        => self::_parseFloat($value->lng),
				'zoom'       => $value->zoom,
				'parts'      => $value->parts,
				'what3words' => $value->what3words,
			],

			'defaultValue' => [
				'address'    => null,
				'lat'        => self::_parseFloat($this->lat),
				'lng'        => self::_parseFloat($this->lng),
				'zoom'       => $this->zoom,
				'parts'      => null,
				'what3words' => null,
			],
		];

		// Map Services
		// ---------------------------------------------------------------------

		if (strpos($settings->mapTiles, 'google') !== false)
		{
			if ($settings->getMapToken() !== $settings->getGeoToken())
			{
				$view->registerJsFile(
					'https://maps.googleapis.com/maps/api/js?key=' .
					$settings->getMapToken()
				);
			}
		}
		elseif (strpos($settings->mapTiles, 'mapkit') !== false)
		{
			$view->registerJsFile(
				'https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js'
			);
		}

		// Geo Services
		// ---------------------------------------------------------------------

		if ($settings->geoService === GeoEnum::GoogleMaps)
		{
			$view->registerJsFile(
				'https://maps.googleapis.com/maps/api/js?libraries=places&key=' .
				$settings->getGeoToken()
			);
		}
		elseif ($settings->geoService === GeoEnum::AppleMapKit)
		{
			$view->registerJsFile(
				'https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js'
			);
		}

		// what3words
		// ---------------------------------------------------------------------

		if ($settings->w3wEnabled && !empty($settings->getW3WToken()))
		{
			$view->registerJsFile(
				'https://assets.what3words.com/sdk/v3/what3words.js?key=' .
				$settings->getW3WToken()
			);
		}

		// ---------------------------------------------------------------------

		$options = preg_replace(
			'/\'/',
			'&#039;',
			json_encode($opts)
		);

		if ($this->size === 'normal')
			$children = '<div style="height:360px"></div>';
		else
			$children = $value->address;

		return '<div id="' . $containerId . '"><simple-map options=\'' . $options . '\'>' . $children . '</simple-map></div>';
	}

	// Helpers
	// =========================================================================

	/**
	 * Will cast the given value to a float if not null
	 *
	 * @param null $value
	 *
	 * @return float|null
	 */
	private static function _parseFloat ($value = null) {
		if ($value === null)
			return null;

		return (float) $value;
	}

}
