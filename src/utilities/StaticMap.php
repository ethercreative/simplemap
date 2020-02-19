<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\utilities;

use Craft;
use craft\helpers\Json;
use craft\web\Response;
use ether\simplemap\enums\MapTiles;
use ether\simplemap\models\Marker;
use ether\simplemap\models\Point;
use ether\simplemap\models\Settings;
use ether\simplemap\SimpleMap;
use GuzzleHttp\Client;
use Imagine\Image\Box;
use Imagine\Image\FontInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point\Center;
use Imagine\Imagick\Imagick;

/**
 * Class StaticMap
 *
 * Based off https://github.com/dfacts/staticmaplite/blob/master/staticmap.php
 *
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
	private $markers;

	/**
	 * @var ImageInterface
	 */
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
	 * @param string|null $markers
	 *
	 * @throws \Exception
	 */
	public function __construct (
		$lat = 51.272154,
		$lng = 0.514951,
		$width = 640,
		$height = 480,
		$zoom = 15,
		$scale = 1,
		$markers = null
	) {
		$this->lat    = $lat;
		$this->lng    = $lng;
		$this->width  = $width;
		$this->height = $height;
		$this->zoom   = $zoom;
		$this->scale  = $scale;

		if (empty($markers)) $this->markers = [];
		else
		{
			$this->markers = array_map(function ($m) {
				$m = explode('|', $m);

				return new Marker([
					'location' => Json::decode($m[0]),
					'color' => $m[1],
					'label' => $m[2],
				]);
			}, explode(';', $markers));
		}

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
		$this->_placeMarkers();

		self::_mkdirRecursive(dirname($filename), 0777);
		$this->image->save($filename);

		if (file_exists($filename))
			return $this->_send(file_get_contents($filename));

		return $this->_send($this->image->show('png'));
	}

	// Private Methods
	// =========================================================================

	private function _initCoords ()
	{
		$this->centerX = $this->_lngToTile($this->lng);
		$this->centerY = $this->_latToTile($this->lat);
	}

	private function _createBaseMap ()
	{
		$imagine = $this->_getImagine();
		$palette = new RGB();

		$w = $this->width * $this->scale;
		$h = $this->height * $this->scale;
		$_ts = $this->tileSize * $this->scale;

		$this->image = $imagine->create(new Box($w, $h));

		$startX = floor($this->centerX - ($w / $_ts) / 2);
		$startY = floor($this->centerY - ($h / $_ts) / 2);

		$endX = ceil($this->centerX + ($w / $_ts) / 2);
		$endY = ceil($this->centerY + ($h / $_ts) / 2);

		$this->offsetX = -floor(($this->centerX - floor($this->centerX)) * $_ts);
		$this->offsetY = -floor(($this->centerY - floor($this->centerY)) * $_ts);

		$this->offsetX += floor($w / 2);
		$this->offsetY += floor($h / 2);

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

				if ($tileData) {
					$tileImg = $imagine->load($tileData);
				} else {
					$tileImg = $imagine->create(new Box($_ts, $_ts));
					$tileImg->draw()->text(
						'err',
						null,
						new Point($_ts / 2, $_ts / 2),
						$palette->color('#fff', 100)
					);
				}

				$destX = ($x - $startX) * $_ts + $this->offsetX;
				$destY = ($y - $startY) * $_ts + $this->offsetY;

				$this->image->paste(
					$tileImg,
					new Point($destX, $destY)
				);
			}
		}
	}

	private function _placeMarkers ()
	{
		$w   = $this->width * $this->scale;
		$h   = $this->height * $this->scale;
		$_ts = $this->tileSize * $this->scale;

		/** @var Marker $marker */
		foreach ($this->markers as $marker)
		{
			$img = $this->_renderMarker(
				$marker->color,
				$marker->label
			);

			$pos = explode(',', $marker->getLocation(true));

			$x = floor(($w / 2) - $_ts * ($this->centerX - $this->_lngToTile($pos[1])));
			$y = floor(($h / 2) - $_ts * ($this->centerY - $this->_latToTile($pos[0])));

			$x -= $img->getSize()->getWidth() / 2;
			$y -= $img->getSize()->getHeight();

			$this->image->paste(
				$img,
				new Point($x, $y)
			);
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

	public static function getLabelColour ($color)
	{
		$r = hexdec($color[1] . $color[2]);
		$g = hexdec($color[3] . $color[4]);
		$b = hexdec($color[5] . $color[6]);

		return (($r * 299 + $g * 587 + $b * 114) / 1000 > 130) ? '000' : 'fff';
	}

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

	// Imagine
	// -------------------------------------------------------------------------

	private function _getImageDriver ()
	{
		static $driver;

		if ($driver)
			return $driver;

		$generalConfig = Craft::$app->getConfig()->getGeneral();
		$extension     = strtolower($generalConfig->imageDriver);

		if ($extension === 'gd' || Craft::$app->getImages()->getIsGd())
			$driver = 'gd';
		else
			$driver = 'imagick';

		return $driver;
	}

	private function _getImagine ()
	{
		static $imagine;

		if ($imagine)
			return $imagine;

		if ($this->_getImageDriver() === 'gd') {
			$imagine = new \Imagine\Gd\Imagine();
		} else {
			$imagine = new \Imagine\Imagick\Imagine();
		}

		return $imagine;
	}

	private function _getFont (ColorInterface $colour, $size = 10)
	{
		$key = ((string) $colour) . '-' . $size;

		/** @var FontInterface[] $fonts */
		static $fonts = [];

		if (array_key_exists($key, $fonts))
			return $fonts[$key];

		$file = Craft::getAlias('@simplemap/resources/OpenSans-Bold.ttf');

		if ($this->_getImageDriver() === 'gd')
			$fonts[$key] = new \Imagine\Gd\Font($file, $size, $colour);
		else
			$fonts[$key] = new \Imagine\Imagick\Font(new Imagick(), $file, $size, $colour);

		return $fonts[$key];
	}

	private function _renderMarker ($colour, $label = null)
	{
		$resizeMultiplier = 0.1 * $this->scale;
		$fontSize = 12 * $this->scale;
		$fontOffset = 4 * $this->scale;

		$svg = $label === null ? 'markerNoLabel.png' : 'marker.png';

		$img = $this->_getImagine()->open(
			Craft::getAlias('@simplemap/resources/' . $svg)
		);
		$img->resize(new Box(
			$img->getSize()->getWidth() * $resizeMultiplier,
			$img->getSize()->getHeight() * $resizeMultiplier
		), ImageInterface::FILTER_MITCHELL);
		$img->effects()->colorize($img->palette()->color($colour));

		if ($label !== null)
		{
			$textColour = $img->palette()->color(self::getLabelColour($colour));
			$imgCenter = new Center($img->getSize());
			$font = $this->_getFont($textColour, $fontSize);
			$textCenter = new Center($font->box($label));

			$img->draw()->text(
				$label,
				$font,
				new Point(
					$imgCenter->getX() - $textCenter->getX(),
					$fontOffset
				)
			);
		}

		return $img;
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
				'markers' => $this->markers,
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
