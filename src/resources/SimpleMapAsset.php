<?php

namespace ether\simplemap\resources;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SimpleMapAsset extends AssetBundle
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
			'SimpleMap_Map.js',
		];

		$this->css = [
			'SimpleMap_Map.css',
		];

		parent::init();
	}

}