<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\controllers;

use craft\web\Controller;
use ether\simplemap\utilities\StaticMap;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * Class StaticController
 *
 * @author  Ether Creative
 * @package ether\simplemap\controllers
 */
class StaticController extends Controller
{

	protected $allowAnonymous = true;

	/**
	 * @throws BadRequestHttpException
	 * @throws \Exception
	 */
	public function actionIndex ()
	{
		$request = \Craft::$app->getRequest();

		if (!$request->validateCsrfToken($request->getRequiredQueryParam('csrf')))
			throw new BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));

		return (new StaticMap(
			$request->getRequiredQueryParam('lat'),
			$request->getRequiredQueryParam('lng'),
			$request->getRequiredQueryParam('width'),
			$request->getRequiredQueryParam('height'),
			$request->getRequiredQueryParam('zoom'),
			$request->getRequiredQueryParam('scale'),
			$request->getQueryParam('markers')
		))->render();
	}

}
