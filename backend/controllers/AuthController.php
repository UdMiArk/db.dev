<?php


namespace backend\controllers;


use backend\components\BackendController;
use backend\models\FormLogin;
use common\data\RBACData;
use common\models\Resource;
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
						'roles' => [$this->authFullPermissions],
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

	protected function _getBasePermissions(\yii\web\User $user) {
		$result = [];

		if ($user->can(RBACData::ROLE_SUPERUSER)) {
			$result['canManageUsers'] = true;
			$result['canManageResourceTypes'] = true;
		}
		if ($user->can(Resource::RBAC_VIEW, ['index' => true])) {
			$result['canViewResources'] = true;
			if ($user->can(Resource::RBAC_CREATE)) {
				$result['canCreateResources'] = true;
			}
			if ($user->can(Resource::RBAC_APPROVE, ['index' => true])) {
				$result['canModerateResources'] = true;
			}
		}

		return $result;
	}

	protected function _prepareAuthData(\yii\web\User $user) {
		$result = [
			'csrfToken' => $this->request->getCsrfToken(),
			'domains' => (new FormLogin())->getAvailableProviders(),
		];
		if ($user->isGuest) {
			$result['authenticated'] = false;
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
				'permissions' => $this->_getBasePermissions($user),
			]);
			$result['simpleUser'] = !(
				@$result['permissions']['canModerateResources']
				||
				Resource::find()->andWhere(['user_id' => $identity->primaryKey])->exists()
			);
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