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
		return '1.3.0';
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
			'browserApiKey' => array(AttributeType::String)
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('simplemap/plugin-settings', array(
			'settings' => $this->getSettings()
		));
	}

}
