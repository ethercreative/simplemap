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
		return '1.0.1';
	}

	public function getDeveloper()
	{
		return 'Ether Creative';
	}

	public function getDeveloperUrl()
	{
		return 'http://ethercreative.co.uk';
	}

}