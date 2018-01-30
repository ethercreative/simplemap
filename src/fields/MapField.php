<?php

namespace ether\simplemap\fields;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Json;
use ether\simplemap\resources\MapSettingsAsset;
use ether\simplemap\resources\SimpleMapAsset;
use ether\simplemap\services\MapService;
use ether\simplemap\SimpleMap;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class MapField extends Field implements PreviewableFieldInterface
{

	// Props
	// =========================================================================

	// Props: Public Instance
	// -------------------------------------------------------------------------

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
	 * @var int - The height of the map in pixels
	 */
	public $height = 400;

	/**
	 * @var bool - If true, the map will not be displayed
	 */
	public $hideMap = false;

	/**
	 * @var bool - If true, the lat/lng inputs will not be displayed
	 */
	public $hideLatLng = true;

	/**
	 * @var string|null - The country to restrict the location search to
	 */
	public $countryRestriction;

	/**
	 * @var string|null - The location types to restrict the location search to
	 */
	public $typeRestriction;

	/**
	 * @var float|null - The north east latitude of the map bounds
	 */
	public $boundaryRestrictionNELat;

	/**
	 * @var float|null - The north east longitude of the map bounds
	 */
	public $boundaryRestrictionNELng;

	/**
	 * @var float|null - The south west latitude of the map bounds
	 */
	public $boundaryRestrictionSWLat;

	/**
	 * @var float|null - The south west longitude of the map bounds
	 */
	public $boundaryRestrictionSWLng;

	/**
	 * @var string - The formatted boundary for the map
	 */
	public $boundary = '""';

	// Props: Private Static
	// -------------------------------------------------------------------------

	/**
	 * @var array - All the countries of the world, at least, for now...
	 */
	private static $_countries = [
		['value' => '', 'label' => 'All Countries'],
		['value' => 'af', 'label' => 'Afghanistan'],
		['value' => 'ax', 'label' => 'Åland Islands'],
		['value' => 'al', 'label' => 'Albania'],
		['value' => 'dz', 'label' => 'Algeria'],
		['value' => 'as', 'label' => 'American Samoa'],
		['value' => 'ad', 'label' => 'Andorra'],
		['value' => 'ao', 'label' => 'Angola'],
		['value' => 'ai', 'label' => 'Anguilla'],
		['value' => 'aq', 'label' => 'Antarctica'],
		['value' => 'ag', 'label' => 'Antigua and Barbuda'],
		['value' => 'ar', 'label' => 'Argentina'],
		['value' => 'am', 'label' => 'Armenia'],
		['value' => 'aw', 'label' => 'Aruba'],
		['value' => 'au', 'label' => 'Australia'],
		['value' => 'at', 'label' => 'Austria'],
		['value' => 'az', 'label' => 'Azerbaijan'],
		['value' => 'bs', 'label' => 'Bahamas'],
		['value' => 'bh', 'label' => 'Bahrain'],
		['value' => 'bd', 'label' => 'Bangladesh'],
		['value' => 'bb', 'label' => 'Barbados'],
		['value' => 'by', 'label' => 'Belarus'],
		['value' => 'be', 'label' => 'Belgium'],
		['value' => 'bz', 'label' => 'Belize'],
		['value' => 'bj', 'label' => 'Benin'],
		['value' => 'bm', 'label' => 'Bermuda'],
		['value' => 'bt', 'label' => 'Bhutan'],
		['value' => 'bo', 'label' => 'Bolivia, Plurinational State of'],
		['value' => 'bq', 'label' => 'Bonaire, Sint Eustatius and Saba'],
		['value' => 'ba', 'label' => 'Bosnia and Herzegovina'],
		['value' => 'bw', 'label' => 'Botswana'],
		['value' => 'bv', 'label' => 'Bouvet Island'],
		['value' => 'br', 'label' => 'Brazil'],
		['value' => 'io', 'label' => 'British Indian Ocean Territory'],
		['value' => 'bn', 'label' => 'Brunei Darussalam'],
		['value' => 'bg', 'label' => 'Bulgaria'],
		['value' => 'bf', 'label' => 'Burkina Faso'],
		['value' => 'bi', 'label' => 'Burundi'],
		['value' => 'kh', 'label' => 'Cambodia'],
		['value' => 'cm', 'label' => 'Cameroon'],
		['value' => 'ca', 'label' => 'Canada'],
		['value' => 'cv', 'label' => 'Cape Verde'],
		['value' => 'ky', 'label' => 'Cayman Islands'],
		['value' => 'cf', 'label' => 'Central African Republic'],
		['value' => 'td', 'label' => 'Chad'],
		['value' => 'cl', 'label' => 'Chile'],
		['value' => 'cn', 'label' => 'China'],
		['value' => 'cx', 'label' => 'Christmas Island'],
		['value' => 'cc', 'label' => 'Cocos (Keeling) Islands'],
		['value' => 'co', 'label' => 'Colombia'],
		['value' => 'km', 'label' => 'Comoros'],
		['value' => 'cg', 'label' => 'Congo'],
		['value' => 'cd', 'label' => 'Congo, the Democratic Republic of the'],
		['value' => 'ck', 'label' => 'Cook Islands'],
		['value' => 'cr', 'label' => 'Costa Rica'],
		['value' => 'ci', 'label' => 'Côte d\'Ivoire'],
		['value' => 'hr', 'label' => 'Croatia'],
		['value' => 'cu', 'label' => 'Cuba'],
		['value' => 'cw', 'label' => 'Curaçao'],
		['value' => 'cy', 'label' => 'Cyprus'],
		['value' => 'cz', 'label' => 'Czech Republic'],
		['value' => 'dk', 'label' => 'Denmark'],
		['value' => 'dj', 'label' => 'Djibouti'],
		['value' => 'dm', 'label' => 'Dominica'],
		['value' => 'do', 'label' => 'Dominican Republic'],
		['value' => 'ec', 'label' => 'Ecuador'],
		['value' => 'eg', 'label' => 'Egypt'],
		['value' => 'sv', 'label' => 'El Salvador'],
		['value' => 'gq', 'label' => 'Equatorial Guinea'],
		['value' => 'er', 'label' => 'Eritrea'],
		['value' => 'ee', 'label' => 'Estonia'],
		['value' => 'et', 'label' => 'Ethiopia'],
		['value' => 'fk', 'label' => 'Falkland Islands (Malvinas)'],
		['value' => 'fo', 'label' => 'Faroe Islands'],
		['value' => 'fj', 'label' => 'Fiji'],
		['value' => 'fi', 'label' => 'Finland'],
		['value' => 'fr', 'label' => 'France'],
		['value' => 'gf', 'label' => 'French Guiana'],
		['value' => 'pf', 'label' => 'French Polynesia'],
		['value' => 'tf', 'label' => 'French Southern Territories'],
		['value' => 'ga', 'label' => 'Gabon'],
		['value' => 'gm', 'label' => 'Gambia'],
		['value' => 'ge', 'label' => 'Georgia'],
		['value' => 'de', 'label' => 'Germany'],
		['value' => 'gh', 'label' => 'Ghana'],
		['value' => 'gi', 'label' => 'Gibraltar'],
		['value' => 'gr', 'label' => 'Greece'],
		['value' => 'gl', 'label' => 'Greenland'],
		['value' => 'gd', 'label' => 'Grenada'],
		['value' => 'gp', 'label' => 'Guadeloupe'],
		['value' => 'gu', 'label' => 'Guam'],
		['value' => 'gt', 'label' => 'Guatemala'],
		['value' => 'gg', 'label' => 'Guernsey'],
		['value' => 'gn', 'label' => 'Guinea'],
		['value' => 'gw', 'label' => 'Guinea-Bissau'],
		['value' => 'gy', 'label' => 'Guyana'],
		['value' => 'ht', 'label' => 'Haiti'],
		['value' => 'hm', 'label' => 'Heard Island and McDonald Islands'],
		['value' => 'va', 'label' => 'Holy See (Vatican City State)'],
		['value' => 'hn', 'label' => 'Honduras'],
		['value' => 'hk', 'label' => 'Hong Kong'],
		['value' => 'hu', 'label' => 'Hungary'],
		['value' => 'is', 'label' => 'Iceland'],
		['value' => 'in', 'label' => 'India'],
		['value' => 'id', 'label' => 'Indonesia'],
		['value' => 'ir', 'label' => 'Iran, Islamic Republic of'],
		['value' => 'iq', 'label' => 'Iraq'],
		['value' => 'ie', 'label' => 'Ireland'],
		['value' => 'im', 'label' => 'Isle of Man'],
		['value' => 'il', 'label' => 'Israel'],
		['value' => 'it', 'label' => 'Italy'],
		['value' => 'jm', 'label' => 'Jamaica'],
		['value' => 'jp', 'label' => 'Japan'],
		['value' => 'je', 'label' => 'Jersey'],
		['value' => 'jo', 'label' => 'Jordan'],
		['value' => 'kz', 'label' => 'Kazakhstan'],
		['value' => 'ke', 'label' => 'Kenya'],
		['value' => 'ki', 'label' => 'Kiribati'],
		['value' => 'kp', 'label' => 'Korea, Democratic People\'s Republic of'],
		['value' => 'kr', 'label' => 'Korea, Republic of'],
		['value' => 'kw', 'label' => 'Kuwait'],
		['value' => 'kg', 'label' => 'Kyrgyzstan'],
		['value' => 'la', 'label' => 'Lao People\'s Democratic Republic'],
		['value' => 'lv', 'label' => 'Latvia'],
		['value' => 'lb', 'label' => 'Lebanon'],
		['value' => 'ls', 'label' => 'Lesotho'],
		['value' => 'lr', 'label' => 'Liberia'],
		['value' => 'ly', 'label' => 'Libya'],
		['value' => 'li', 'label' => 'Liechtenstein'],
		['value' => 'lt', 'label' => 'Lithuania'],
		['value' => 'lu', 'label' => 'Luxembourg'],
		['value' => 'mo', 'label' => 'Macao'],
		['value' => 'mk', 'label' => 'Macedonia, the former Yugoslav Republic of'],
		['value' => 'mg', 'label' => 'Madagascar'],
		['value' => 'mw', 'label' => 'Malawi'],
		['value' => 'my', 'label' => 'Malaysia'],
		['value' => 'mv', 'label' => 'Maldives'],
		['value' => 'ml', 'label' => 'Mali'],
		['value' => 'mt', 'label' => 'Malta'],
		['value' => 'mh', 'label' => 'Marshall Islands'],
		['value' => 'mq', 'label' => 'Martinique'],
		['value' => 'mr', 'label' => 'Mauritania'],
		['value' => 'mu', 'label' => 'Mauritius'],
		['value' => 'yt', 'label' => 'Mayotte'],
		['value' => 'mx', 'label' => 'Mexico'],
		['value' => 'fm', 'label' => 'Micronesia, Federated States of'],
		['value' => 'md', 'label' => 'Moldova, Republic of'],
		['value' => 'mc', 'label' => 'Monaco'],
		['value' => 'mn', 'label' => 'Mongolia'],
		['value' => 'me', 'label' => 'Montenegro'],
		['value' => 'ms', 'label' => 'Montserrat'],
		['value' => 'ma', 'label' => 'Morocco'],
		['value' => 'mz', 'label' => 'Mozambique'],
		['value' => 'mm', 'label' => 'Myanmar'],
		['value' => 'na', 'label' => 'Namibia'],
		['value' => 'nr', 'label' => 'Nauru'],
		['value' => 'np', 'label' => 'Nepal'],
		['value' => 'nl', 'label' => 'Netherlands'],
		['value' => 'nc', 'label' => 'New Caledonia'],
		['value' => 'nz', 'label' => 'New Zealand'],
		['value' => 'ni', 'label' => 'Nicaragua'],
		['value' => 'ne', 'label' => 'Niger'],
		['value' => 'ng', 'label' => 'Nigeria'],
		['value' => 'nu', 'label' => 'Niue'],
		['value' => 'nf', 'label' => 'Norfolk Island'],
		['value' => 'mp', 'label' => 'Northern Mariana Islands'],
		['value' => 'no', 'label' => 'Norway'],
		['value' => 'om', 'label' => 'Oman'],
		['value' => 'pk', 'label' => 'Pakistan'],
		['value' => 'pw', 'label' => 'Palau'],
		['value' => 'ps', 'label' => 'Palestine, State of'],
		['value' => 'pa', 'label' => 'Panama'],
		['value' => 'pg', 'label' => 'Papua New Guinea'],
		['value' => 'py', 'label' => 'Paraguay'],
		['value' => 'pe', 'label' => 'Peru'],
		['value' => 'ph', 'label' => 'Philippines'],
		['value' => 'pn', 'label' => 'Pitcairn'],
		['value' => 'pl', 'label' => 'Poland'],
		['value' => 'pt', 'label' => 'Portugal'],
		['value' => 'pr', 'label' => 'Puerto Rico'],
		['value' => 'qa', 'label' => 'Qatar'],
		['value' => 're', 'label' => 'Réunion'],
		['value' => 'ro', 'label' => 'Romania'],
		['value' => 'ru', 'label' => 'Russian Federation'],
		['value' => 'rw', 'label' => 'Rwanda'],
		['value' => 'bl', 'label' => 'Saint Barthélemy'],
		['value' => 'sh', 'label' => 'Saint Helena, Ascension and Tristan da Cunha'],
		['value' => 'kn', 'label' => 'Saint Kitts and Nevis'],
		['value' => 'lc', 'label' => 'Saint Lucia'],
		['value' => 'mf', 'label' => 'Saint Martin (French part)'],
		['value' => 'pm', 'label' => 'Saint Pierre and Miquelon'],
		['value' => 'vc', 'label' => 'Saint Vincent and the Grenadines'],
		['value' => 'ws', 'label' => 'Samoa'],
		['value' => 'sm', 'label' => 'San Marino'],
		['value' => 'st', 'label' => 'Sao Tome and Principe'],
		['value' => 'sa', 'label' => 'Saudi Arabia'],
		['value' => 'sn', 'label' => 'Senegal'],
		['value' => 'rs', 'label' => 'Serbia'],
		['value' => 'sc', 'label' => 'Seychelles'],
		['value' => 'sl', 'label' => 'Sierra Leone'],
		['value' => 'sg', 'label' => 'Singapore'],
		['value' => 'sx', 'label' => 'Sint Maarten (Dutch part)'],
		['value' => 'sk', 'label' => 'Slovakia'],
		['value' => 'si', 'label' => 'Slovenia'],
		['value' => 'sb', 'label' => 'Solomon Islands'],
		['value' => 'so', 'label' => 'Somalia'],
		['value' => 'za', 'label' => 'South Africa'],
		['value' => 'gs', 'label' => 'South Georgia and the South Sandwich Islands'],
		['value' => 'ss', 'label' => 'South Sudan'],
		['value' => 'es', 'label' => 'Spain'],
		['value' => 'lk', 'label' => 'Sri Lanka'],
		['value' => 'sd', 'label' => 'Sudan'],
		['value' => 'sr', 'label' => 'Suriname'],
		['value' => 'sj', 'label' => 'Svalbard and Jan Mayen'],
		['value' => 'sz', 'label' => 'Swaziland'],
		['value' => 'se', 'label' => 'Sweden'],
		['value' => 'ch', 'label' => 'Switzerland'],
		['value' => 'sy', 'label' => 'Syrian Arab Republic'],
		['value' => 'tw', 'label' => 'Taiwan, Province of China'],
		['value' => 'tj', 'label' => 'Tajikistan'],
		['value' => 'tz', 'label' => 'Tanzania, United Republic of'],
		['value' => 'th', 'label' => 'Thailand'],
		['value' => 'tl', 'label' => 'Timor-Leste'],
		['value' => 'tg', 'label' => 'Togo'],
		['value' => 'tk', 'label' => 'Tokelau'],
		['value' => 'to', 'label' => 'Tonga'],
		['value' => 'tt', 'label' => 'Trinidad and Tobago'],
		['value' => 'tn', 'label' => 'Tunisia'],
		['value' => 'tr', 'label' => 'Turkey'],
		['value' => 'tm', 'label' => 'Turkmenistan'],
		['value' => 'tc', 'label' => 'Turks and Caicos Islands'],
		['value' => 'tv', 'label' => 'Tuvalu'],
		['value' => 'ug', 'label' => 'Uganda'],
		['value' => 'ua', 'label' => 'Ukraine'],
		['value' => 'ae', 'label' => 'United Arab Emirates'],
		['value' => 'gb', 'label' => 'United Kingdom'],
		['value' => 'us', 'label' => 'United States'],
		['value' => 'um', 'label' => 'United States Minor Outlying Islands'],
		['value' => 'uy', 'label' => 'Uruguay'],
		['value' => 'uz', 'label' => 'Uzbekistan'],
		['value' => 'vu', 'label' => 'Vanuatu'],
		['value' => 've', 'label' => 'Venezuela, Bolivarian Republic of'],
		['value' => 'vn', 'label' => 'Viet Nam'],
		['value' => 'vg', 'label' => 'Virgin Islands, British'],
		['value' => 'vi', 'label' => 'Virgin Islands, U.S.'],
		['value' => 'wf', 'label' => 'Wallis and Futuna'],
		['value' => 'eh', 'label' => 'Western Sahara'],
		['value' => 'ye', 'label' => 'Yemen'],
		['value' => 'zm', 'label' => 'Zambia'],
		['value' => 'zw', 'label' => 'Zimbabwe'],
	];

	/**
	 * @var array - The types of features google's location search can be
	 *     restricted by
	 */
	private static $_types = [
		['label' => 'Any Type', 'value' => ''],
		['label' => 'Non-business Locations', 'value' => 'geocode'],
		['label' => 'Precise Addresses', 'value' => 'address'],
		['label' => 'Businesses Only', 'value' => 'establishment'],
		['label' => 'Regions (Countries, States, Counties, Postal Codes, etc...)', 'value' => '(regions)'],
		['label' => 'Towns & Cities', 'value' => '(cities)'],
	];

	// Public Functions
	// =========================================================================

	// Public Functions: Static
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 */
	public static function displayName (): string
	{
		return \Craft::t('simplemap', 'Map');
	}

	/**
	 * @inheritdoc
	 */
	public static function hasContentColumn (): bool
	{
		return false;
	}

	// Public Functions: Instance
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 */
	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['lat', 'lng', 'zoom', 'height'],
			'required',
		];

		$rules[] = [
			['lat', 'boundaryRestrictionNELat', 'boundaryRestrictionSWLat'],
			'double',
			'min' => -90,
			'max' => 90,
		];

		$rules[] = [
			['lng', 'boundaryRestrictionNELng', 'boundaryRestrictionSWLng'],
			'double',
			'min' => -180,
			'max' => 180,
		];

		return $rules;
	}

	/**
	 * @inheritdoc
	 */
	public function getSettingsHtml ()
	{
		$view         = \Craft::$app->getView();
		$key          = SimpleMap::$plugin->getSettings()->apiKey;
		$locale       = \Craft::$app->locale->id;
		$namespacedId = $view->namespaceInputId('');
		$boundary     = $this->_getBoundary();

		if ($boundary === null) {
			$emptyLatLng = [ 'lat' => 0, 'lng' => 0 ];
			$boundary = [
				'nw' => $emptyLatLng,
				'se' => $emptyLatLng,
			];
		}

		$settings = Json::encode([
			'lat' => $this->lat,
			'lng' => $this->lng,
			'zoom' => $this->zoom,
			'height' => $this->height,
			'boundary' => $boundary,
		]);

		$view->registerAssetBundle(MapSettingsAsset::class);
		$view->registerJs("new SimpleMapSettings(
	'{$key}', 
	'{$locale}',
	'{$namespacedId}',
	{$settings}
);");

		return \Craft::$app->getView()->renderTemplate(
			'simplemap/field-settings',
			[
				'countries' => self::$_countries,
				'types'     => self::$_types,
				'field'     => $this
			]
		);
	}

	/**
	 * @inheritdoc
	 */
	public function getInputHtml (
		$value,
		ElementInterface $element = null
	): string {

		/** @var Element $element */

		$view = \Craft::$app->getView();

		$id           = $view->formatInputId($this->handle);
		$namespacedId = $view->namespaceInputId($id);

		if ($boundary = $this->_getBoundary())
			$this->boundary = $boundary;

		$key     = SimpleMap::$plugin->getSettings()->apiKey;
		$locale  = $element ? $element->siteId : \Craft::$app->locale->id;
		$hideMap = $this->hideMap ? 'true' : 'false';

		$view->registerAssetBundle(SimpleMapAsset::class);
		$view->registerJs(
			"new SimpleMap(
	'{$key}', 
	'{$namespacedId}', 
	{
		lat: '{$this->lat}', 
		lng: '{$this->lng}', 
		zoom: '{$this->zoom}', 
		height: '{$this->height}', 
		hideMap: {$hideMap}, 
		country: '{$this->countryRestriction}', 
		type: '{$this->typeRestriction}', 
		boundary: {$this->boundary}
	}, 
	'{$locale}'
);");

		return $view->renderTemplate(
			'simplemap/field-input',
			[
				'id'    => $id,
				'name'  => $this->handle,
				'value' => $value,
				'field' => $this,
				'height'=> $this->height,
			]
		);
	}

	/**
	 * @inheritdoc
	 */
	public function normalizeValue ($value, ElementInterface $element = null)
	{
		return SimpleMap::$plugin->getMap()->getField($this, $element, $value);
	}

	/**
	 * @inheritdoc
	 */
	public function getElementValidationRules (): array
	{
		return [
			[MapValidator::class, 'on' => Element::SCENARIO_LIVE],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function modifyElementsQuery (ElementQueryInterface $query, $value)
	{
		// For whatever reason, this function can be
		// run BEFORE SimpleMap has been initialized
		if (!SimpleMap::$plugin)
			return null;

		SimpleMap::$plugin->getMap()->modifyElementsQuery($query, $value);

		return null;
	}

	// Public Functions: Events
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 */
	public function afterElementSave (ElementInterface $element, bool $isNew)
	{
		SimpleMap::$plugin->getMap()->saveField($this, $element);
		parent::afterElementSave($element, $isNew);
	}

	// Helpers
	// =========================================================================

	private function _getBoundary ()
	{
		if (
			$this->boundaryRestrictionNELat
			&& $this->boundaryRestrictionNELng
			&& $this->boundaryRestrictionSWLat
			&& $this->boundaryRestrictionSWLng
		) {
			$ne = [
				'lat' => $this->boundaryRestrictionNELat,
				'lng' => $this->boundaryRestrictionNELng,
			];

			$sw = [
				'lat' => $this->boundaryRestrictionSWLat,
				'lng' => $this->boundaryRestrictionSWLng,
			];

			return json_encode(['ne' => $ne, 'sw' => $sw]);
		}

		return null;
	}

}