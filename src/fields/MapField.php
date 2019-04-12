<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\maps\fields;

use Craft;
use craft\base\EagerLoadingFieldInterface;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\db\Query;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Json;
use ether\maps\enums\GeoService as GeoEnum;
use ether\maps\models\Settings;
use ether\maps\services\GeoService;
use ether\maps\Maps;
use ether\maps\web\assets\MapAsset;
use ether\maps\elements\Map as MapElement;
use ether\maps\records\Map as MapRecord;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Class Map
 *
 * @author  Ether Creative
 * @package ether\maps\fields
 */
class MapField extends Field implements EagerLoadingFieldInterface, PreviewableFieldInterface
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
		return Maps::t('Map');
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
			['zoom'],
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
		if (is_array($value) && !empty($value[0]))
			$value = $value[0];

		if ($value instanceof MapElement)
			return $value;

		if ($value instanceof ElementQueryInterface)
			return $value->one();

		if (is_string($value))
			$value = Json::decodeIfJson($value);

		$map = null;

		if ($element && $element->id)
		{
			/** @var MapElement $map */
			$map = MapElement::find()
				->anyStatus()
				->fieldId($this->id)
				->ownerSiteId($element->siteId)
				->ownerId($element->id)
				->trashed($element->trashed)
				->one();

			if ($map && $value)
			{
				$map->lat     = $value['lat'];
				$map->lng     = $value['lng'];
				$map->zoom    = $value['zoom'];
				$map->address = $value['address'];
				$map->parts   = $value['parts'];
			}
		}

		if ($map === null)
		{
			if (is_array($value))
				$map = new MapElement($value);
			else
				$map = new MapElement([
					'lat' => null,
					'lng' => null,
					'zoom' => $this->zoom,
				]);
		}

		$map->ownerId = $element->id;
		$map->ownerSiteId = $element->siteId;
		$map->fieldId = $this->id;

		$handle = $this->handle;
		$element->$handle = $map;

		return $map;
	}

	/**
	 * @return string|Markup|null
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 * @throws InvalidConfigException
	 */
	public function getSettingsHtml ()
	{
		$value = new MapElement();

		$value->lat  = $this->lat;
		$value->lng  = $this->lng;
		$value->zoom = $this->zoom;

		$originalHandle      = $this->handle;
		$originalCountry     = $this->country;
		$originalHideSearch  = $this->hideSearch;
		$originalHideMap     = $this->hideMap;
		$originalHideAddress = $this->hideAddress;

		$this->handle      = '__settings__';
		$this->country     = null;
		$this->hideSearch  = false;
		$this->hideMap     = false;
		$this->hideAddress = true;

		$mapField = new Markup(
			$this->_renderMap($value, true),
			'utf-8'
		);

		$this->handle      = $originalHandle;
		$this->country     = $originalCountry;
		$this->hideSearch  = $originalHideSearch;
		$this->hideMap     = $originalHideMap;
		$this->hideAddress = $originalHideAddress;

		$view = Craft::$app->getView();

		$countries = array_merge([
			'*' => Maps::t('All Countries'),
		], GeoService::$countries);

		/** @noinspection PhpComposerExtensionStubsInspection */
		return $view->renderTemplate('maps/field-settings', [
			'map' => $mapField,
			'field' => $this,
			'countries' => $countries,
		]);
	}

	/**
	 * @param MapElement $value
	 * @param ElementInterface|Element|null $element
	 *
	 * @return string
	 * @throws InvalidConfigException
	 */
	public function getInputHtml ($value, ElementInterface $element = null): string
	{
		if ($element !== null && $element->hasEagerLoadedElements($this->handle))
			$value = $element->getEagerLoadedElements($this->handle);

		/** @noinspection PhpComposerExtensionStubsInspection */
		return new Markup(
			$this->_renderMap($value),
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
	 */
	public function getTableAttributeHtml ($value, ElementInterface $element): string
	{
		return $this->normalizeValue($value, $element)->address;
	}

	/**
	 * @inheritdoc
	 *
	 * @param array $sourceElements
	 *
	 * @return array
	 */
	public function getEagerLoadingMap (array $sourceElements)
	{
		$sourceElementIds = [];
		$sourceSiteIds = [];

		foreach ($sourceElements as $sourceElement)
		{
			$sourceElementIds[] = $sourceElement->id;
			$sourceSiteIds[] = $sourceElement->siteId;
		}

		$map = (new Query())
			->select(['ownerId as source', 'id as target'])
			->from([MapRecord::TableName])
			->where([
				'fieldId' => $this->id,
				'ownerId' => $sourceElementIds,
				'ownerSiteId' => $sourceSiteIds,
			])
			->all();

		return [
			'elementType' => MapElement::class,
			'map' => $map,
			'criteria' => ['fieldId' => $this->id],
		];
	}

	/**
	 * @param ElementQueryInterface $query
	 * @param                       $value
	 *
	 * @return bool|false|null
	 * @throws \yii\db\Exception
	 */
	public function modifyElementsQuery (ElementQueryInterface $query, $value)
	{
		if (!Maps::getInstance())
			return null;

		Maps::getInstance()->map->modifyElementsQuery($query, $value, $this);

		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function isValueEmpty ($value, ElementInterface $element): bool
	{
		return empty($value->lat) && empty($value->lng);
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
		if (!Maps::getInstance()->map->validateField($this, $element))
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
		Maps::getInstance()->map->saveField($this, $element);
		parent::afterElementSave($element, $isNew);
	}

	/**
	 * @param ElementInterface|Element $element
	 *
	 * @throws Throwable
	 */
	public function afterElementDelete (ElementInterface $element)
	{
		Maps::getInstance()->map->softDeleteField($this, $element);
		parent::afterElementDelete($element);
	}

	/**
	 * @param ElementInterface|Element $element
	 *
	 * @throws Throwable
	 * @throws Exception
	 */
	public function afterElementRestore (ElementInterface $element)
	{
		Maps::getInstance()->map->restoreField($this, $element);
		parent::afterElementRestore($element);
	}

	// Helpers
	// =========================================================================

	/**
	 * Renders the map input
	 *
	 * @param      $value
	 * @param bool $isSettings
	 *
	 * @return string
	 * @throws InvalidConfigException
	 */
	private function _renderMap ($value, $isSettings = false)
	{
		$view = Craft::$app->getView();

		$containerId = $this->id . '-container';
		$vueContainerId = $view->namespaceInputId($containerId);
		$view->registerJsFile('https://polyfill.io/v3/polyfill.min.js?flags=gated&features=default%2CIntersectionObserver%2CIntersectionObserverEntry');
		$view->registerAssetBundle(MapAsset::class);
		$view->registerJs('new Vue({ el: \'#' . $vueContainerId . '\' });');
		$view->registerTranslations('maps', [
			'Search for a location',
			'Clear',
			'Name / Number',
			'Street Address',
			'Town / City',
			'Postcode',
			'County',
			'State',
			'Country',
			'Latitude',
			'Longitude',
		]);

		/** @var Settings $settings */
		$settings = Maps::getInstance()->getSettings();

		$country = $this->country;
		// Convert ISO2 to ISO3 for Here autocomplete
		if ($country && $settings->geoService === GeoEnum::Here)
			$country = GeoService::$countriesIso3[$country];

		$opts = [
			'config' => [
				'isSettings' => $isSettings,

				'name'        => $view->namespaceInputName($this->handle),
				'country'     => $country,
				'hideSearch'  => (bool) $this->hideSearch,
				'hideMap'     => (bool) $this->hideMap,
				'hideAddress' => (bool) $this->hideAddress,
				'showLatLng'  => (bool) $this->showLatLng,

				'mapTiles' => $settings->mapTiles,
				'mapToken' => GeoService::getToken(
					$settings->mapToken,
					$settings->mapTiles
				),

				'geoService' => $settings->geoService,
				'geoToken'   => GeoService::getToken(
					$settings->geoToken,
					$settings->geoService
				),

				'locale' => Craft::$app->locale->getLanguageID(),
			],

			'value' => [
				'address' => $value->address,
				'lat'     => $value->lat,
				'lng'     => $value->lng,
				'zoom'    => $value->zoom,
				'parts'   => $value->parts,
			],

			'defaultValue' => [
				'address' => null,
				'lat'     => $this->lat,
				'lng'     => $this->lng,
				'zoom'    => $this->zoom,
				'parts'   => null,
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

		if ($settings->geoService === GeoEnum::GoogleMaps)
		{
			$view->registerJsFile(
				'https://maps.googleapis.com/maps/api/js?libraries=places&key=' .
				$settings->geoToken
			);
		}
		elseif ($settings->geoService === GeoEnum::AppleMapKit)
		{
			$view->registerJsFile(
				'https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js'
			);
		}

		$options = preg_replace(
			'/\'/',
			'&#039;',
			json_encode($opts)
		);

		return '<div id="' . $containerId . '"><maps-map options=\'' . $options . '\'></maps-map></div>';
	}

}
