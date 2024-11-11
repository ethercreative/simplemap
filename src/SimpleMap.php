<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterGqlTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\services\Fields;
use craft\services\Gql;
use craft\web\Application;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\wpimport\Command as WpImportCommand;
use ether\simplemap\acfadapters\GoogleMap as GoogleMapAcfAdapter;
use ether\simplemap\fields\MapField as MapField;
use ether\simplemap\integrations\feedme\FeedMeMaps;
use ether\simplemap\integrations\graphql\MapPartsType;
use ether\simplemap\integrations\graphql\MapType;
use ether\simplemap\models\Settings;
use ether\simplemap\services\EmbedService;
use ether\simplemap\services\GeoLocationService;
use ether\simplemap\services\MapService;
use ether\simplemap\services\StaticService;
use ether\simplemap\web\Variable;
use Exception;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Class SimpleMap
 *
 * @author  Ether Creative
 * @package ether\simplemap
 * @property MapService $map
 * @property StaticService $static
 * @property EmbedService $embed
 * @property GeoLocationService $geolocation
 */
class SimpleMap extends Plugin
{

	const EDITION_LITE = 'lite';
	const EDITION_PRO = 'pro';

	// Properties
	// =========================================================================

	public bool $hasCpSettings = true;

	// Static
	// =========================================================================

	public static function editions (): array
	{
		return [
			self::EDITION_LITE,
			self::EDITION_PRO,
		];
	}

	// Craft
	// =========================================================================

	public function init ()
	{
		parent::init();

		Craft::setAlias(
			'simplemap',
			__DIR__
		);

		Craft::setAlias(
			'simplemapimages',
			__DIR__ . '/web/assets/imgs'
		);

		$this->setComponents([
			'map' => MapService::class,
			'static' => StaticService::class,
			'embed' => EmbedService::class,
			'geolocation' => GeoLocationService::class,
		]);

		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			[$this, 'onRegisterCPUrlRules']
		);

		Event::on(
			Fields::class,
			Fields::EVENT_REGISTER_FIELD_TYPES,
			[$this, 'onRegisterFieldTypes']
		);

		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			[$this, 'onRegisterVariable']
		);

		if (class_exists(Gql::class))
		{
			Event::on(
				Gql::class,
				Gql::EVENT_REGISTER_GQL_TYPES,
				[$this, 'onRegisterGqlTypes']
			);
		}

		if (class_exists(\craft\feedme\Plugin::class))
		{
			Event::on(
				\craft\feedme\services\Fields::class,
				\craft\feedme\services\Fields::EVENT_REGISTER_FEED_ME_FIELDS,
				[$this, 'onRegisterFeedMeFields']
			);
		}

		$request = Craft::$app->getRequest();
		if (
			!$request->getIsConsoleRequest()
			&& $request->getMethod() === 'GET'
			&& $request->getIsSiteRequest()
			&& !$request->getIsPreview()
			&& !$request->getIsActionRequest()
		) {
			Event::on(
				Application::class,
				Application::EVENT_INIT,
				[$this, 'onApplicationInit']
			);
		}

		if (class_exists(WpImportCommand::class)) {
			Event::on(
				WpImportCommand::class,
				WpImportCommand::EVENT_REGISTER_ACF_ADAPTERS,
				static function (RegisterComponentTypesEvent $event) {
					$event->types[] = GoogleMapAcfAdapter::class;
				}
			);
		}
	}

	protected function beforeUninstall (): void
	{
		if ($this->getSettings()->geoLocationService === GeoLocationService::MaxMindLite)
			GeoLocationService::purgeDb();
	}

	// Settings
	// =========================================================================

	protected function createSettingsModel (): Model
	{
		return new Settings();
	}

	/**
	 * @return Model
	 */
	public function getSettings (): Model
	{
		return parent::getSettings();
	}

	protected function settingsHtml (): ?string
	{
		// Redirect to our settings page
		Craft::$app->controller->redirect(
			UrlHelper::cpUrl('maps/settings')
		);

		return null;
	}

	public function afterSaveSettings (): void
	{
		parent::afterSaveSettings();

		$service = $this->getSettings()->geoLocationService;

		if ($service !== GeoLocationService::MaxMindLite)
			GeoLocationService::purgeDb();
		else if (!GeoLocationService::dbExists())
			GeoLocationService::dbQueueDownload();
	}

	// Events
	// =========================================================================

	public function onRegisterCPUrlRules (RegisterUrlRulesEvent $event)
	{
		$event->rules['maps/settings'] = 'simplemap/settings';
	}

	public function onRegisterFieldTypes (RegisterComponentTypesEvent $event)
	{
		$event->types[] = MapField::class;
	}

	/**
	 * @param Event $event
	 *
	 * @throws InvalidConfigException
	 */
	public function onRegisterVariable (Event $event)
	{
		/** @var CraftVariable $variable */
		$variable = $event->sender;
		$variable->set('simpleMap', Variable::class);
		$variable->set('maps', Variable::class);
	}

	public function onRegisterFeedMeFields (\craft\feedme\events\RegisterFeedMeFieldsEvent $event)
	{
		$event->fields[] = FeedMeMaps::class;
	}

	public function onRegisterGqlTypes (RegisterGqlTypesEvent $event)
	{
		$event->types[] = MapType::class;
		$event->types[] = MapPartsType::class;
	}

	/**
	 * @throws Exception
	 */
	public function onApplicationInit ()
	{
		if ($this->getSettings()->geoLocationAutoRedirect)
			$this->geolocation->redirect();
	}

	// Helpers
	// =========================================================================

	public static function t ($message, $params = []): string
	{
		return Craft::t('simplemap', $message, $params);
	}

	public static function v ($version, $operator = '='): bool
	{
		return SimpleMap::getInstance()->is($version, $operator);
	}

}
