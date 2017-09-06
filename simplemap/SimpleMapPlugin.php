<?php

namespace Craft;

/**
 * SimpleMap for Craft CMS
 *
 * @author    Ether Creative <hello@ethercreative.co.uk>
 * @copyright Copyright (c) 2015, Ether Creative
 * @license   http://ether.mit-license.org/
 * @since     1.0
 */
class SimpleMapPlugin extends BasePlugin {

	public function getName()
	{
		return Craft::t('Simple Map');
	}

	public function getDescription()
	{
		return 'A beautifully simple Google Map field type for Craft CMS.';
	}

	public function getVersion()
	{
		return '1.6.3';
	}

	public function getSchemaVersion()
	{
		return '0.0.6';
	}

	public function getDeveloper()
	{
		return 'Ether Creative';
	}

	public function getDeveloperUrl()
	{
		return 'http://ethercreative.co.uk';
	}

	public function getDocumentationUrl()
	{
		return 'https://github.com/ethercreative/SimpleMap/blob/master/README.md';
	}

	public function getReleaseFeedUrl()
	{
		return 'https://raw.githubusercontent.com/ethercreative/SimpleMap/master/releases.json';
	}

	protected function defineSettings()
	{
		return array(
			'browserApiKey' => array(AttributeType::String),
			'serverApiKey' => array(AttributeType::String),
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('simplemap/plugin-settings', array(
			'settings' => $this->getSettings()
		));
	}

	public function init ()
	{
		Craft::import('plugins.simplemap.integrations.feedme.fields.SimpleMap_MapFeedMeFieldType');
	}

	// ====================================================================== //
	// For compatibility with Feed Me plugin (v2.x)
	public function registerFeedMeFieldTypes()
	{
		return array(
			new SimpleMap_MapFeedMeFieldType(),
		);
	}

}
