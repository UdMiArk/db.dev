<?php


namespace backend\controllers;


use backend\components\BackendController;
use backend\models\FormLogin;
use common\models\User;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class AuthController extends BackendController {
	public function behaviors() {
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'actions' => ['index', 'logout', 'login'],
						'allow' => true,
					],
					[
						'allow' => true,
						'roles' => [$this->authMinimalLevel],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'index' => ['GET'],
					'*' => ['POST'],
				],
			],
		];
	}

	public function actionIndex() {
		return $this->asJson($this->_prepareAuthData(\Yii::$app->user));
	}

	protected function _prepareAuthData(\yii\web\User $user) {
		$result = [
			'csrf_token' => $this->request->getCsrfToken(),
		];
		if ($user->isGuest) {
			$result['authenticated'] = false;
			$result['user'] = null;
		} else {
			/* @var User $identity */
			$identity = $user->identity;
			$result = array_merge($result, [
				'authenticated' => true,
				'user' => [
					'__id' => $identity->primaryKey,
					'name' => $identity->name,
					'email' => $identity->email,
				],
			]);
		}
		return $result;
	}

	public function actionLogin() {
		$user = \Yii::$app->user;
		if (!$user->isGuest) {
			$user->logout();
		}
		$model = new FormLogin();
		$model->load($this->request->post());
		if (!$model->execute()) {
			return $this->asJson([
				'success' => false,
				'error' => $model->getFirstError(),
				'errors' => $model->getErrors(),
			]);
		}
		return $this->asJson([
			'success' => true,
			'auth' => $this->_prepareAuthData($user),
		]);
	}

	public function actionLogout() {
		$user = \Yii::$app->user;
		return $this->asJson([
			'success' => $user->logout(),
			'auth' => $this->_prepareAuthData($user),
		]);
	}
}