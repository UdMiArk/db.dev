<?php


namespace backend\components;

use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\queue\Queue;
use yii\web\Controller;
use yii\web\Request;

/**
 * Class BackendController
 * @package backend\components
 *
 * @property-read Request $request
 * @property-read User $user
 * @property-read Queue $queue
 */
class BackendController extends Controller {
	//public $enableCsrfValidation = false;
	public $authFullPermissions = '@';

	public function behaviors() {
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => [$this->authFullPermissions],
					],
				],
			],
		];
	}

	public function getRequest() {
		return Yii::$app->request;
	}

	/**
	 * @return User|null
	 */
	public function getUser() {
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return Yii::$app->user->identity;
	}

	/**
	 * @return Queue
	 */
	public function getQueue() {
		return Yii::$app->queue;
	}
}