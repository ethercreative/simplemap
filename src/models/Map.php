<?php

namespace ether\simplemap\models;

use craft\base\Model;

class Map extends Model
{

	// Props
	// =========================================================================

	// Props: Public Instance
	// -------------------------------------------------------------------------

	/** @var int|null */
	public $ownerId;

	/** @var int|null */
	public $ownerSiteId;

	/** @var int|null */
	public $fieldId;

	/** @var float|null */
	public $lat;

	/** @var float|null */
	public $lng;

	/** @var int|null */
	public $zoom;

	/** @var string|null */
	public $address;

	/** @var array|null */
	public $parts;

	/** @var float|null */
	public $distance;

	// Public Methods
	// =========================================================================

	public function __construct ($attributes = [], array $config = [])
	{
		foreach ($attributes as $key => $value)
			if (property_exists($this, $key))
				$this[$key] = $value;

		if (is_string($this->parts))
			$this->parts = json_decode($this->parts, true);

		parent::__construct($config);
	}

	public function __toString (): string
	{
		return $this->address ?: '';
	}

	// Public Methods: Instance
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 */
	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['lat', 'lng', 'zoom', 'height'],
			'required',
		];

		$rules[] = [
			['lat', 'boundaryRestrictionNELat', 'boundaryRestrictionSWLat'],
			'double',
			'min' => -90,
			'max' => 90,
		];

		$rules[] = [
			['lng', 'boundaryRestrictionNELng', 'boundaryRestrictionSWLng'],
			'double',
			'min' => -180,
			'max' => 180,
		];

		return $rules;
	}

}