<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\models;

use Imagine\Image\BoxInterface;
use Imagine\Image\PointInterface;

/**
 * Class Point
 *
 * Custom Point class to allow negative coordinates
 *
 * @author  Ether Creative
 * @package ether\simplemap\models
 */
class Point implements PointInterface
{

	/**
	 * @var int
	 */
	private $x;

	/**
	 * @var int
	 */
	private $y;

	/**
	 * Constructs a point of coordinates.
	 *
	 * @param int $x
	 * @param int $y
	 */
	public function __construct ($x, $y)
	{
		$this->x = $x;
		$this->y = $y;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Imagine\Image\PointInterface::getX()
	 */
	public function getX ()
	{
		return $this->x;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Imagine\Image\PointInterface::getY()
	 */
	public function getY ()
	{
		return $this->y;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Imagine\Image\PointInterface::in()
	 */
	public function in (BoxInterface $box)
	{
		return $this->x < $box->getWidth() && $this->y < $box->getHeight();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Imagine\Image\PointInterface::move()
	 */
	public function move ($amount)
	{
		return new self($this->x + $amount, $this->y + $amount);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see \Imagine\Image\PointInterface::__toString()
	 */
	public function __toString ()
	{
		return sprintf('(%d, %d)', $this->x, $this->y);
	}

}
