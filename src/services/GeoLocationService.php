<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\services;

use craft\base\Component;
use ether\simplemap\SimpleMap;

/**
 * Class GeoLocationService
 *
 * @author  Ether Creative
 * @package ether\simplemap\services
 */
class GeoLocationService extends Component
{

	// Consts
	// =========================================================================

	const None = 'none';

	const IpStack = 'ipstack';

	const MaxMindLite = 'maxmind-lite';

	const MaxMind = 'maxmind';

	// Helpers
	// =========================================================================

	public static function getSelectOptions ()
	{
		return [
			self::None => SimpleMap::t('None'),
			self::IpStack => SimpleMap::t('ipstack'),
			self::MaxMindLite => SimpleMap::t('MaxMind (Lite, ~60MB download)'),
			self::MaxMind => SimpleMap::t('MaxMind'),
		];
	}

}
