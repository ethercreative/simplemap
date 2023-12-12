<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use craft\helpers\Json;
use craft\helpers\Typecast;
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

	/** @var float|null */
	public ?float $lat = null;

	/** @var float|null */
	public ?float $lng = null;

	/** @var string|null */
	public ?string $address = null;

	/** @var PartsLegacy|Parts|array|null */
	public PartsLegacy|Parts|array|null $parts = null;

	/** @var string|null */
	public ?string $what3words = null;

	// Constructor
	// =========================================================================

	public function __construct ($config = [])
	{
		Typecast::properties(static::class, $config);
		
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
	 * @param array  $exclude - An array of parts to exclude from the output
	 * @param string $glue    - The glue to join the parts together
	 *
	 * @return Markup
	 */
	public function address (array $exclude = [], string $glue = '<br/>'): Markup
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
