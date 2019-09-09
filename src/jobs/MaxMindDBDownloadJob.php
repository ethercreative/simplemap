<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\jobs;

use Craft;
use craft\helpers\FileHelper;
use craft\queue\BaseJob;
use craft\queue\QueueInterface;
use ether\simplemap\services\GeoLocationService;

/**
 * Class MaxMindDBDownloadJob
 *
 * @author  Ether Creative
 * @package ether\simplemap\jobs
 */
class MaxMindDBDownloadJob extends BaseJob
{

	protected function defaultDescription ()
	{
		return 'Downloading MaxMind DB';
	}

	/**
	 * @param \yii\queue\Queue|QueueInterface $queue The queue the job belongs to
	 *
	 * @throws \Exception
	 */
	public function execute ($queue)
	{
		try {
			$temp   = tempnam(sys_get_temp_dir(), 'mmdb');
			$target = Craft::getAlias(
				GeoLocationService::DB_STORAGE . DIRECTORY_SEPARATOR . 'default.mmdb'
			);

			$client = Craft::createGuzzleClient();
			$client->get(
				'https://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz',
				[
					'save_to' => $temp,
					'progress' => function ($total, $current) use ($queue) {
						if ($total > 0)
							$queue->setProgress(($current / $total) * 100);
					},
				]
			);

			$extracted = '';
			$gz        = gzopen($temp, 'r');

			while ($data = gzread($gz, 10000000))
				$extracted .= $data;

			gzclose($gz);

			if (!file_exists($target))
				FileHelper::createDirectory(pathinfo($target)['dirname']);

			$saved = file_put_contents($target, $extracted);
			@unlink($temp);

			if (!$saved)
				Craft::error('Unable to save MaxMind DB!', 'maps');

			Craft::$app->getCache()->delete('maps_db_updating');
		} catch (\Exception $e) {
			Craft::$app->getCache()->delete('maps_db_updating');
			throw $e;
		}
	}

}
