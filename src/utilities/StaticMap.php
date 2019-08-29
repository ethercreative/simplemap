<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\utilities;

/**
 * Class StaticMap
 *
 * TODO: Build from https://github.com/dfacts/staticmaplite/blob/master/staticmap.php
 *
 * @author  Ether Creative
 * @package ether\simplemap\utilities
 */
class StaticMap
{

	// Properties
	// =========================================================================

	private $lat, $lng, $width, $height, $zoom, $scale;

	// Constructor
	// =========================================================================

	public function __construct ($lat, $lng, $width, $height, $zoom, $scale)
	{
		$this->lat    = $lat;
		$this->lng    = $lng;
		$this->width  = $width;
		$this->height = $height;
		$this->zoom   = $zoom;
		$this->scale  = $scale;
	}

	// Methods
	// =========================================================================

	public function render ()
	{
		header("Content-Type: image/png");
		$im = @imagecreate($this->width, $this->height) or die("Cannot Initialize new GD image stream");
		$background_color = imagecolorallocate($im, 0, 0, 0);
		$text_color       = imagecolorallocate($im, 233, 14, 91);
		imagestring($im, 1, 5, 5, "TODO: Static map", $text_color);
		imagepng($im);
		imagedestroy($im);
	}

}
