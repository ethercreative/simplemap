<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap;

use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use ether\simplemap\enums\GeoService;
use ether\simplemap\enums\MapTiles;
use ether\simplemap\fields\Map as MapField;
use ether\simplemap\integrations\craftql\GetCraftQLSchema;
use ether\simplemap\models\Settings;
use ether\simplemap\services\MapService;
use ether\simplemap\web\Variable;
use yii\base\Event;

/**
 * Class SimpleMap
 *
 * @author  Ether Creative
 * @package ether\simplemap
 * @property MapService $map
 */
class SimpleMap extends Plugin
{

	// Properties
	// =========================================================================

	public $hasCpSettings = true;

	// Craft
	// =========================================================================

	public function init ()
	{
		parent::init();

		\Craft::setAlias(
			'simplemapimages',
			__DIR__ . '/web/assets/imgs'
		);

		$this->setComponents([
			'map' => MapService::class,
		]);

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

		if (class_exists(\markhuot\CraftQL\CraftQL::class))
		{
			Event::on(
				MapField::class,
				'craftQlGetFieldSchema',
				[new GetCraftQLSchema, 'handle']
			);
		}
	}

	// Settings
	// =========================================================================

	protected function createSettingsModel ()
	{
		return new Settings();
	}

	/**
	 * @return string|null
	 * @throws \Twig\Error\LoaderError
	 * @throws \Twig\Error\RuntimeError
	 * @throws \Twig\Error\SyntaxError
	 */
	protected function settingsHtml ()
	{
		return \Craft::$app->getView()->renderTemplate(
			'simplemap/settings',
			[
				'settings' => $this->getSettings(),
				'mapTileOptions' => MapTiles::getSelectOptions(),
				'geoServiceOptions' => GeoService::getSelectOptions(),
			]
		);
	}

	// Events
	// =========================================================================

	public function onRegisterFieldTypes (RegisterComponentTypesEvent $event)
	{
		$event->types[] = MapField::class;
	}

	/**
	 * @param Event $event
	 *
	 * @throws \yii\base\InvalidConfigException
	 */
	public function onRegisterVariable (Event $event)
	{
		/** @var CraftVariable $variable */
		$variable = $event->sender;
		$variable->set('simpleMap', Variable::class);
		$variable->set('maps', Variable::class);
	}

	// Helpers
	// =========================================================================

	public static function t ($message, $params = [])
	{
		return \Craft::t('simplemap', $message, $params);
	}

}