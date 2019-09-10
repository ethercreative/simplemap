<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use craft\helpers\Template;
use craft\web\View;
use ether\simplemap\enums\MapTiles;
use ether\simplemap\models\EmbedOptions;
use ether\simplemap\models\Settings;
use ether\simplemap\SimpleMap;

/**
 * Class EmbedService
 *
 * @author  Ether Creative
 * @package ether\simplemap\services
 */
class EmbedService extends Component
{

	// Constants
	// =========================================================================

	const JSON_OPTS = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

	// Embed
	// =========================================================================

	/**
	 * @param array $options
	 *
	 * @return string|void
	 * @throws \yii\base\InvalidConfigException
	 */
	public function embed ($options = [])
	{
		if (SimpleMap::v(SimpleMap::EDITION_LITE))
			return 'Sorry, embed maps are a Maps Pro feature!';

		$options = new EmbedOptions($options);

		/** @var Settings $settings */
		$settings = SimpleMap::getInstance()->getSettings();

		switch ($settings->mapTiles)
		{
			case MapTiles::GoogleHybrid:
			case MapTiles::GoogleRoadmap:
			case MapTiles::GoogleTerrain:
				$code = $this->_embedGoogle($options, $settings);
				break;
			case MapTiles::MapKitHybrid:
			case MapTiles::MapKitMutedStandard:
			case MapTiles::MapKitSatellite:
			case MapTiles::MapKitStandard:
				$code = $this->_embedApple($options, $settings);
				break;
			case MapTiles::MapboxDark:
			case MapTiles::MapboxLight:
			case MapTiles::MapboxOutdoors:
			case MapTiles::MapboxStreets:
				$code = $this->_embedMapbox($options, $settings);
				break;
			case MapTiles::HereHybrid:
			case MapTiles::HereNormalDay:
			case MapTiles::HereNormalDayGrey:
			case MapTiles::HereNormalDayTransit:
			case MapTiles::HerePedestrian:
			case MapTiles::HereReduced:
			case MapTiles::HereSatellite:
			case MapTiles::HereTerrain:
				$code = $this->_embedHere($options, $settings);
				break;
			default:
				$code = $this->_embedDefault($options);
		}

		return Template::raw($code);
	}

	// Embed-ers
	// =========================================================================

	private function _embedGoogle (EmbedOptions $options, Settings $settings)
	{
		$view = Craft::$app->getView();
		$callbackName = 'init_' . $options->id;

		switch ($settings->mapTiles)
		{
			case MapTiles::GoogleRoadmap:
				$mapTypeId = 'roadmap';
				break;
			case MapTiles::GoogleTerrain:
				$mapTypeId = 'terrain';
				break;
			case MapTiles::GoogleHybrid:
			default:
				$mapTypeId = 'hybrid';
		}

		$formattedOptions = Json::encode(
			array_merge(
				$options->options,
				[
					'center'    => $options->getCenter(),
					'zoom'      => $options->zoom,
					'mapTypeId' => $mapTypeId,
				]
			),
			self::JSON_OPTS
		);

		$formattedMarkers = [];

		foreach ($options->markers as $marker)
			$formattedMarkers[] = [
				'position' => $marker->getCenter(),
				'label' => $marker->label,
				// TODO: Add custom colour support
			];

		$formattedMarkers = Json::encode(
			$formattedMarkers,
			self::JSON_OPTS
		);

		$params = http_build_query([
			'key' => $settings->mapToken,
			'callback' => $callbackName,
		]);

		$this->_js(
			'https://maps.googleapis.com/maps/api/js?' . $params,
			['async' => '', 'defer' => '']
		);

		$js = <<<JS
let {$options->id};

function {$callbackName} () {
	{$options->id} = new google.maps.Map(document.getElementById('{$options->id}'), $formattedOptions);
	
	{$options->id}._markers = [];
	{$formattedMarkers}.forEach(function (marker) {
		marker.map = {$options->id};
		{$options->id}._markers.push(new google.maps.Marker(marker));
	});
}
JS;

		$css = <<<CSS
#{$options->id} {
	width: {$options->width}px;
	height: {$options->height}px;
}
CSS;


		$view->registerJs($js, View::POS_END);
		$view->registerCss($css);

		return '<div id="' . $options->id . '"></div>';
	}

	private function _embedApple (EmbedOptions $options, Settings $settings)
	{
		//
	}

	private function _embedMapbox (EmbedOptions $options, Settings $settings)
	{
		//
	}

	private function _embedHere (EmbedOptions $options, Settings $settings)
	{
		//
	}

	private function _embedDefault (EmbedOptions $options)
	{
		//
	}

	// Helpers
	// =========================================================================

	/**
	 * @param string $url     - The URL to the JS file
	 * @param array  $options - An array of options to be used as attributes
	 * @param string $pre     - Will link rel="pre${pre}" if not null
	 */
	private function _js ($url, $options = [], $pre = 'connect')
	{
		$view = Craft::$app->getView();

		if ($pre)
		{
			$crossOrigin = !$this->_compareUrls(
				$url,
				Craft::$app->sites->currentSite->baseUrl
			);

			$view->registerLinkTag(
				[
					'rel'         => 'pre' . $pre,
					'href'        => $url,
					'as'          => 'script',
					'crossorigin' => $crossOrigin,
				]
			);
		}

		$view->registerScript(
			'',
			View::POS_END,
			array_merge(
				['src' => $url],
				$options
			),
			md5($url)
		);
	}

	private function _compareUrls ($a, $b)
	{
		$a = parse_url($a, PHP_URL_HOST);
		$b = parse_url($b, PHP_URL_HOST);

		return $this->_trim($a) === $this->_trim($b);
	}

	private function _trim ($str)
	{
		if (stripos($str, 'www.') === 0)
			return substr($str, 4);

		return $str;
	}

}
