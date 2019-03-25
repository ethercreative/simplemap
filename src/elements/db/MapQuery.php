<?php
/**
 * SimpleMap for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\elements\db;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use craft\models\Site;
use ether\simplemap\records\Map;

/**
 * Class MapQuery
 *
 * @author  Ether Creative
 * @package ether\simplemap\elements\db
 */
class MapQuery extends ElementQuery
{

	// Properties
	// =========================================================================

	/** @var int */
	public $fieldId;

	/** @var int */
	public $ownerId;

	/** @var int */
	public $ownerSiteId;

	// Public Methods
	// =========================================================================

	public function fieldId ($value)
	{
		$this->fieldId = $value;

		return $this;
	}

	public function ownerId ($value)
	{
		$this->ownerId = $value;

		return $this;
	}

	public function ownerSiteId ($value)
	{
		$this->ownerSiteId = $value;

		if ($value && strtolower($value) !== ':empty:')
			$this->ownerSiteId = (int) $value;

		return $this;
	}

	/**
	 * @param string|Site $value
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function ownerSite ($value)
	{
		if ($value instanceof Site)
		{
			$this->ownerSiteId($value->id);
		}
		else
		{
			$site = \Craft::$app->getSites()->getSiteByHandle($value);

			if (!$site)
				throw new \Exception('Invalid site handle: ' . $value);

			$this->ownerSiteId($site->id);
		}

		return $this;
	}

	public function owner (ElementInterface $owner)
	{
		/** @var Element $owner */
		$this->ownerId = $owner->id;
		$this->siteId = $owner->siteId;

		return $this;
	}

	// Protected Methods
	// =========================================================================

	protected function beforePrepare (): bool
	{
		$table = Map::TableNameClean;

		$this->joinElementTable($table);

		$this->query->select($table . '.*');

		if ($this->fieldId)
			$this->subQuery->andWhere(
				Db::parseParam($table . '.fieldId', $this->fieldId)
			);

		if ($this->ownerId)
			$this->subQuery->andWhere(
				Db::parseParam($table . '.ownerId', $this->ownerId)
			);

		if ($this->ownerSiteId)
			$this->subQuery->andWhere(
				Db::parseParam($table . '.ownerSiteId', $this->ownerSiteId)
			);

		return parent::beforePrepare();
	}

}