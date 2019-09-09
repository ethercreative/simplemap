<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use craft\helpers\StringHelper;

/**
 * Class EmbedOptions
 *
 * @author  Ether Creative
 * @package ether\simplemap\models
 */
class EmbedOptions extends StaticOptions
{

	// Properties
	// =========================================================================

	/** @var string The ID of the map (unique ID will be generated if null) */
	public $id;

	/** @var array Options to be passed to the JS map */
	public $options = [];

	// Constructor
	// =========================================================================

	public function __construct ($config = [])
	{
		parent::__construct($config);

		if (!$this->id)
			$this->id = StringHelper::appendUniqueIdentifier('map');
	}

}
