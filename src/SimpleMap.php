<?php

namespace ether\SimpleMap;

use Craft;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\UrlHelper;
use craft\services\Fields;
use craft\services\Plugins;
use ether\SimpleMap\fields\MapField;
use ether\SimpleMap\models\Settings;
use yii\base\Event;

class SimpleMap extends Plugin
{

	// Properties
	// =========================================================================

	public $changelogUrl = 'https://raw.githubusercontent.com/ethercreative/simplemap/v3/CHANGELOG.md';
	public $downloadUrl = 'https://github.com/ethercreative/simplemap/archive/v3.zip';

	/** @var SimpleMap */
	public static $plugin;

	// Craft
	// =========================================================================

	public function init ()
	{
		parent::init();
		self::$plugin = $this;

		// Register Events
		// ---------------------------------------------------------------------

		// Field Types
		Event::on(
			Fields::className(),
			Fields::EVENT_REGISTER_FIELD_TYPES,
			call_user_func([$this, '_onRegisterFieldTypes'])
		);

		// Redirect to settings after install
		Event::on(
			Plugins::className(),
			Plugins::EVENT_AFTER_INSTALL_PLUGIN,
			call_user_func([$this, '_onAfterInstallPlugin'])
		);
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

	// Events
	// =========================================================================

	private function _onRegisterFieldTypes (RegisterComponentTypesEvent $event)
	{
		$event->types[] = MapField::class;
	}

	private function _onAfterInstallPlugin (PluginEvent $event)
	{
		// TODO: Is this the correct settings URL?

		if (!Craft::$app->getRequest()->getIsConsoleRequest()
		    && ($event->plugin === $this)) {
			Craft::$app->getResponse()->redirect(
				UrlHelper::cpUrl('settings/plugins/simplemap')
			)->send();
		}
	}

}