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
			Fields::className(),
			Fields::EVENT_REGISTER_FIELD_TYPES,
			[$this, 'onRegisterFieldTypes']
		);

		// Redirect to settings after install
		Event::on(
			Plugins::className(),
			Plugins::EVENT_AFTER_INSTALL_PLUGIN,
			[$this, 'onAfterInstallPlugin']
		);

		// Variable
		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			[$this, 'onRegisterVariable']
		);

		// CraftQL Support
        if (class_exists(\markhuot\CraftQL\CraftQL::class)) {
            Event::on(
                MapField::class,
                'craftQlGetFieldSchema',
                [new \ether\simplemap\listeners\GetCraftQLSchema, 'handle']
            );
        }
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

	public function onAfterInstallPlugin (PluginEvent $event)
	{
		if (!Craft::$app->getRequest()->getIsConsoleRequest()
		    && ($event->plugin === $this)) {
			Craft::$app->getResponse()->redirect(
				UrlHelper::cpUrl('settings/plugins/simplemap')
			)->send();
		}
	}

	public function onRegisterVariable (Event $event)
	{
		/** @var CraftVariable $variable */
		$variable = $event->sender;
		$variable->set('simpleMap', Variable::class);
	}

}