<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\simplemap\web\assets;

use craft\web\AssetBundle;

/**
 * Class FieldAsset
 *
 * @author  Ether Creative
 * @package ether\simplemap\web\assets
 */
class FieldAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__ . '/dist';

		$this->js = [
			'Field.js',
		];

		parent::init();
	}

}