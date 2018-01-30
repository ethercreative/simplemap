<?php

namespace ether\simplemap\resources;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class MapSettingsAsset extends AssetBundle
{

	/**
	 * @inheritdoc
	 */
	public function init ()
	{
		$this->sourcePath = '@ether/simplemap/resources';

		$this->depends = [
			CpAsset::class,
		];

		$this->js = [
			'SimpleMapSettings.min.js',
		];

		$this->css = [
			'SimpleMapSettings.css',
		];

		parent::init();
	}

}