<?php


namespace backend\components;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Request;

/**
 * Class BackendController
 * @package backend\components
 *
 * @property-read Request $request
 */
class BackendController extends Controller {
	public $authMinimalLevel = '@';

	public function behaviors() {
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => [$this->authMinimalLevel],
					],
				],
			],
		];
	}

	public function getRequest() {
		return Yii::$app->request;
	}
}