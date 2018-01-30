<?php

namespace ether\simplemap;

use Craft;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\UrlHelper;
use craft\services\Fields;
use craft\services\Plugins;
use craft\web\twig\variables\CraftVariable;
use ether\simplemap\fields\MapField;
use ether\simplemap\listeners\GetCraftQLSchema;
use ether\simplemap\models\Settings;
use ether\simplemap\services\MapService;
use yii\base\Event;

/**
 * Class SimpleMap
 *
 * @package ether\SimpleMap
 */
class SimpleMap extends Plugin
{

	// Props
	// =========================================================================

	// Props: Public Static
	// -------------------------------------------------------------------------

	/** @var SimpleMap */
	public static $plugin;

	// Props: Public Instance
	// -------------------------------------------------------------------------

	public $changelogUrl = 'https://raw.githubusercontent.com/ethercreative/simplemap/v3/CHANGELOG.md';
	public $downloadUrl = 'https://github.com/ethercreative/simplemap/archive/v3.zip';

	// Craft
	// =========================================================================

	public function init ()
	{
		parent::init();
		self::$plugin = $this;

		// Components
		// ---------------------------------------------------------------------

		$this->setComponents([
			'map' => MapService::class,
		]);

		// Register Events
		// ---------------------------------------------------------------------

		// Field Types
		Event::on(
			Fields::class,
			Fields::EVENT_REGISTER_FIELD_TYPES,
			[$this, 'onRegisterFieldTypes']
		);

		// Variable
		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			[$this, 'onRegisterVariable']
		);

		// CraftQL Support
		/** @noinspection PhpUndefinedNamespaceInspection */
		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		if (class_exists(\markhuot\CraftQL\CraftQL::class)) {
            Event::on(
                MapField::class,
                'craftQlGetFieldSchema',
                [new GetCraftQLSchema, 'handle']
            );
        }
	}

	public function afterInstall ()
	{
		parent::afterInstall();

		if (Craft::$app->getRequest()->getIsConsoleRequest())
			return;

		Craft::$app->getResponse()->redirect(
			UrlHelper::cpUrl('settings/plugins/simplemap')
		)->send();
	}

	// Craft: Settings
	// -------------------------------------------------------------------------

	public $hasCpSettings = true;

	protected function createSettingsModel ()
	{
		return new Settings();
	}

	protected function settingsHtml()
	{
		return \Craft::$app->getView()->renderTemplate(
			'simplemap/settings',
			[ 'settings' => $this->getSettings() ]
		);
	}

	// Components
	// =========================================================================

	public function getMap (): MapService
	{
		return $this->map;
	}

	// Events
	// =========================================================================

	public function onRegisterFieldTypes (RegisterComponentTypesEvent $event)
	{
		$event->types[] = MapField::class;
	}

	public function onRegisterVariable (Event $event)
	{
		/** @var CraftVariable $variable */
		$variable = $event->sender;
		$variable->set('simpleMap', Variable::class);
	}

}