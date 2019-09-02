<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\utilities;

use Craft;
use craft\web\Response;
use ether\simplemap\enums\MapTiles;
use ether\simplemap\models\Settings;
use ether\simplemap\SimpleMap;
use GuzzleHttp\Client;

/**
 * Class StaticMap
 *
 * Based off https://github.com/dfacts/staticmaplite/blob/master/staticmap.php
 *
 * TODO: Re-write using https://imagine.readthedocs.io/en/stable/index.html
 * TODO: Support external volumes (i.e. S3, Spaces)
 *
 * @author  Ether Creative
 * @package ether\simplemap\utilities
 */
class StaticMap
{

	// Properties
	// =========================================================================

	const TILE_CACHE_DIR = '@runtime/maps/tiles';
	const MAP_CACHE_DIR  = '@runtime/maps/maps';

	private $lat, $lng, $width, $height, $zoom, $scale;
	private $tiles, $tileSize, $mapTiles;
	private $centerX, $centerY, $offsetX, $offsetY;
	private $image;

	// Constructor
	// =========================================================================

	/**
	 * StaticMap constructor.
	 *
	 * @param float $lat
	 * @param float $lng
	 * @param int $width
	 * @param int $height
	 * @param int $zoom
	 * @param int $scale
	 *
	 * @throws \Exception
	 */
	public function __construct (
		$lat = 51.272154,
		$lng = 0.514951,
		$width = 640,
		$height = 480,
		$zoom = 15,
		$scale = 1
	) {
		$this->lat    = $lat;
		$this->lng    = $lng;
		$this->width  = $width;
		$this->height = $height;
		$this->zoom   = $zoom;
		$this->scale  = $scale;

		/** @var Settings $settings */
		$settings = SimpleMap::getInstance()->getSettings();
		$tiles = MapTiles::getTiles($settings->mapTiles, $scale);
		$this->tiles = $tiles['url'];
		$this->tileSize = $tiles['size'];
		$this->mapTiles = $settings->mapTiles;
	}

	// Public Methods
	// =========================================================================

	public function render ()
	{
		$filename = $this->_mapCacheIdToFilename();

		if ($this->_checkMapCache())
			return $this->_send(file_get_contents($filename));

		$this->_initCoords();
		$this->_createBaseMap();

		self::_mkdirRecursive(dirname($filename), 0777);
		imagepng($this->image, $filename, 9);

		if (file_exists($filename))
			return $this->_send(file_get_contents($filename));

		return $this->_send(imagepng($this->image));
	}

	// Private Methods
	// =========================================================================

	private function _initCoords ()
	{
		$this->centerX = $this->_lngToTile($this->lng);
		$this->centerY = $this->_latToTile($this->lat);

		$_ts = $this->tileSize;
		$this->offsetX = floor((floor($this->centerX) - $this->centerX) * $_ts);
		$this->offsetY = floor((floor($this->centerY) - $this->centerY) * $_ts);
	}

	private function _createBaseMap ()
	{
		$this->image = imagecreatetruecolor($this->width, $this->height);

		$_ts = $this->tileSize;

		$startX = floor($this->centerX - ($this->width / $_ts) / 2);
		$startY = floor($this->centerY - ($this->height / $_ts) / 2);

		$endX = ceil($this->centerX + ($this->width / $_ts) / 2);
		$endY = ceil($this->centerY + ($this->height / $_ts) / 2);

		$this->offsetX = -floor(($this->centerX - floor($this->centerX)) * $_ts);
		$this->offsetY = -floor(($this->centerY - floor($this->centerY)) * $_ts);

		$this->offsetX += floor($this->width / 2);
		$this->offsetY += floor($this->height / 2);

		$this->offsetX += floor($startX - floor($this->centerX)) * $_ts;
		$this->offsetY += floor($startY - floor($this->centerY)) * $_ts;

		for ($x = $startX; $x <= $endX; $x++)
		{
			for ($y = $startY; $y <= $endY; $y++)
			{


				$url = str_replace(
					['{z}', '{x}', '{y}'],
					[$this->zoom, $x, $y],
					$this->tiles
				);

				$tileData = $this->_fetchTile($url);

				if ($tileData) $tileImg = imagecreatefromstring($tileData);
				else {
					$tileImg = imagecreate($_ts, $_ts);
					$color = imagecolorallocate($tileImg, 255, 255, 255);
					@imagestring($tileImg, 1, 127, 127, 'err', $color);
				}

				$color = imagecolorallocate($tileImg, 0, 0, 0);
				@imagestring($tileImg, 1, 10, 10, 'x: ' . $x . ', y: ' . $y, $color);

				$destX = ($x - $startX) * $_ts + $this->offsetX;
				$destY = ($y - $startY) * $_ts + $this->offsetY;

				imagecopy(
					$this->image,
					$tileImg,
					$destX,
					$destY,
					0,
					0,
					$_ts,
					$_ts
				);
			}
		}
	}

	private function _send ($file)
	{
		$response = Craft::$app->getResponse();
		$response->format = Response::FORMAT_RAW;

		$expires = 60 * 60 * 24 * 14;
		$headers = $response->getHeaders();
		$headers->set('content-type', 'image/png');
		$headers->set('cache-control', 'maxage=' . $expires);
		$headers->set('expires', gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

		return $file;
	}

	// Helpers
	// =========================================================================

	private static function _join ()
	{
		$paths = func_get_args();
		$paths = array_map(function ($p) {
			return rtrim($p, '/');
		}, $paths);
		$paths = array_filter($paths);

		return join('/', $paths);
	}

	private static function _mkdirRecursive ($pathname, $mode)
	{
		is_dir(dirname($pathname)) || self::_mkdirRecursive(dirname($pathname), $mode);
		return is_dir($pathname) || mkdir($pathname, $mode);
	}

	// Tiles
	// -------------------------------------------------------------------------

	private function _latToTile ($lat)
	{
		return (1 - log(tan($lat * pi() / 180) + 1 / cos($lat * pi() / 180)) / pi()) / 2 * pow(2, $this->zoom);
	}

	private function _lngToTile ($lng)
	{
		return (($lng + 180) / 360) * pow(2, $this->zoom);
	}

	private function _fetchTile ($url)
	{
		if ($cached = $this->_checkTileCache($url))
			return $cached;

		$client = new Client();
		$res = $client->get($url);
		$tile = $res->getBody();
		$this->_writeTileToCache($url, $tile);

		return $tile;
	}

	// Map
	// -------------------------------------------------------------------------

	private function _getMapId ()
	{
		return md5(
			http_build_query([
				'lat'    => $this->lat,
				'lng'    => $this->lng,
				'width'  => $this->width,
				'height' => $this->height,
				'zoom'   => $this->zoom,
				'scale'  => $this->scale,
				'tiles'  => $this->mapTiles,
			])
		);
	}

	// Cache
	// -------------------------------------------------------------------------

	private static function _tileCache ()
	{
		return Craft::getAlias(self::TILE_CACHE_DIR);
	}

	private static function _mapCache ()
	{
		return Craft::getAlias(self::MAP_CACHE_DIR);
	}

	private function _tileUrlToFilename ($url)
	{
		return self::_join(
			self::_tileCache(),
			str_replace(['http://', 'https://'], '', $url)
		);
	}

	private function _mapCacheIdToFilename ()
	{
		$id = $this->_getMapId();

		return self::_join(
			self::_mapCache(),
			substr($id, 0, 2),
			substr($id, 2, 2),
			substr($id, 4)
		) . '.png';
	}

	private function _checkTileCache ($url)
	{
		$filename = $this->_tileUrlToFilename($url);

		return file_exists($filename) ? file_get_contents($filename) : null;
	}

	private function _checkMapCache ()
	{
		return file_exists($this->_mapCacheIdToFilename());
	}

	private function _writeTileToCache ($url, $data)
	{
		$filename = $this->_tileUrlToFilename($url);
		self::_mkdirRecursive(dirname($filename), 0777);
		file_put_contents($filename, $data);
	}

}
