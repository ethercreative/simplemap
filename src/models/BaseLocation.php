<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use craft\helpers\Json;
use Twig\Markup;
use yii\base\Model;

/**
 * Class BaseLocation
 *
 * @author  Ether Creative
 * @package ether\simplemap\models
 */
abstract class BaseLocation extends Model
{

	// Properties
	// =========================================================================

	/** @var float */
	public $lat;

	/** @var float */
	public $lng;

	/** @var string */
	public $address;

	/** @var Parts */
	public $parts;

	/** @var string */
	public $what3words;

	// Constructor
	// =========================================================================

	public function __construct ($config = [])
	{
		parent::__construct($config);

		if ($this->address === null)
			$this->address = '';

		if ($this->parts === null)
		{
			$this->parts = new Parts();
		}
		else if (!($this->parts instanceof Parts))
		{
			if ($this->parts && !is_array($this->parts))
				$this->parts = Json::decodeIfJson($this->parts, true);

			if (Parts::isLegacy($this->parts))
				$this->parts = new PartsLegacy($this->parts);
			else
				$this->parts = new Parts($this->parts);
		}
	}

	// Methods
	// =========================================================================

	/**
	 * Output the address in an easily formatted way
	 *
	 * @param array $exclude - An array of parts to exclude from the output
	 * @param string $glue   - The glue to join the parts together
	 *
	 * @return Markup
	 */
	public function address ($exclude = [], $glue = '<br/>')
	{
		$addr = [];

		if (!is_array($exclude))
			$exclude = [$exclude];

		foreach ([['number', 'address'], 'city', 'county', 'state', 'postcode', 'country'] as $part)
		{
			if (is_array($part))
			{
				$line = [];

				foreach ($part as $p)
				{
					if (in_array($p, $exclude))
						continue;

					$line[] = $this->parts->$p;
				}

				$addr[] = implode(' ', array_filter($line));
				continue;
			}

			if (in_array($part, $exclude))
				continue;

			$addr[] = $this->parts->$part;
		}

		$addr = array_filter($addr);

		return new Markup(implode($glue, $addr), 'utf8');
	}

}
