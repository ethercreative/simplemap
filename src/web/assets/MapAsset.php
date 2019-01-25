<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\vue\VueAsset;

/**
 * Class MapAsset
 *
 * @author  Ether Creative
 * @package ether\simplemap\web\assets
 */
class MapAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__ . '/map';

		$this->depends = [
			VueAsset::class,
		];

		if (getenv('ETHER_ENVIRONMENT'))
		{
			$this->js = [
				'https://localhost:8080/app.js',
			];
		}
		else
		{
			$this->css = [
				'css/app.css',
			];

			$this->js = [
				'js/app.js',
				'js/chunk-vendors.js',
			];
		}

		parent::init();
	}

}