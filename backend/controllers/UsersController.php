<?php


namespace backend\controllers;


use backend\components\BackendController;
use backend\components\ListRequestProcessor;
use backend\models\FormRegister;
use common\data\EUserStatus;
use common\data\RBACData;
use common\models\Resource;
use common\models\User;
use Yii;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rbac\Role;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class UsersController extends BackendController {
	public $authFullPermissions = User::RBAC_MANAGE;

	public function behaviors() {
		return ArrayHelper::merge(parent::behaviors(), [
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'view' => ['GET'],
					'list' => ['GET'],
					'roles' => ['GET'],

					'*' => ['POST'],
				],
			],
		]);
	}

	public function actionRoles() {
		$auth = Yii::$app->authManager;
		return $this->asJson($this->_prepareRoles($auth->getRoles(), $auth->defaultRoles));
	}

	/**
	 * @param Role[] $roles
	 * @param string[] $exclude
	 * @return array
	 */
	public function _prepareRoles($roles, $exclude = null) {
		$result = [];
		foreach ($roles as $role) {
			$name = @$role->data['name'];
			if ($name && !@$role->data['hidden'] && !($exclude && in_array($role->name, $exclude))) {
				$result [] = [
					'id' => $role->name,
					'name' => $name,
					'group' => @$role->data['group'],
					'description' => $role->description,
				];
			}
		}
		return $result;
	}

	public function actionToggleRole($id) {
		$authManager = Yii::$app->authManager;
		$user = $this->_getRequestItem($id);
		$request = $this->request;
		$role = $authManager->getRole($request->post('role'));
		if (!$role) {
			throw new BadRequestHttpException("Указана неизвестная роль");
		}
		if ($role->name === RBACData::ROLE_SUPERUSER && $user->primaryKey === Yii::$app->user->id) {
			throw new BadRequestHttpException("Вы назначать или снимать роль 'Администратор' с себя");
		}
		$give = boolval(intval($this->request->post('state')));
		$assigned = $authManager->getAssignment($role->name, $user->primaryKey);
		if ($give) {
			if (!$assigned) {
				$authManager->assign($role, $user->primaryKey);
			}
		} else {
			if ($assigned) {
				$authManager->revoke($role, $user->primaryKey);
			}
		}
		return $this->asJson([
			'success' => true,
			'roles' => $this->_getUserRoles($user),
		]);
	}

	/**
	 * @param string|integer $id
	 * @return User
	 * @throws NotFoundHttpException
	 */
	protected function _getRequestItem($id) {
		$model = User::findOne($id);
		if (!$model) {
			throw new NotFoundHttpException("Пользователь не найден");
		}
		return $model;
	}

	public function _getUserRoles(User $user) {
		$result = [];
		foreach (Yii::$app->authManager->getRolesByUser($user->primaryKey) as $role) {
			$result [] = $role->name;
		}
		return $result;
	}

	public function actionList() {
		$listProcessor = new ListRequestProcessor();

		$authManager = Yii::$app->authManager;

		$listProcessor->filterHandlers = [
			'role' => function (Query $query, $value) use ($authManager) {
				$role = $authManager->getRole($value);
				$userIds = $role ? $authManager->getUserIdsByRole($role->name) : [];
				if ($userIds) {
					$query->andWhere(['id' => $userIds]);
				} else {
					$query->emulateExecution();
				}
			},
			'name' => function (Query $query, $value) {
				$query->andWhere(['like', 'name', $value]);
			},
			'email' => function (Query $query, $value) {
				$query->andWhere(['like', 'email', $value]);
			},
			'active' => function (Query $query, $value) {
				if ($value) {
					$query->andWhere(['not', ['status' => EUserStatus::DISABLED]]);
				}
			},
		];

		$query = User::find()->alias('user')->orderBy(['user.id' => SORT_DESC]);

		$listProcessor->apply($query);

		return $this->asJson($listProcessor->prepare($query, [
			'__id' => 'primaryKey',
			'created_at',
			'name',
			'login',
			'domain',
			'email',
			'role' => function ($item) use ($authManager) {
				$userId = $item->primaryKey;
				if ($authManager->checkAccess($userId, RBACData::ROLE_SUPERUSER)) {
					return "Администратор";
				} elseif ($authManager->checkAccess($userId, Resource::RBAC_APPROVE)) {
					return "Модератор";
				} elseif ($authManager->checkAccess($userId, Resource::RBAC_AUTO_APPROVE)) {
					return "Доверенный пользователь";
				} else {
					return null;
				}
			},
			'status',
		]));
	}

	public function actionView($id) {
		return $this->asJson($this->_prepareItem($this->_getRequestItem($id)));
	}

	public function _prepareItem(User $user) {
		$result = $user->getFrontendInfo(true);
		$result['roles'] = $this->_getUserRoles($user);
		return $result;
	}

	public function actionRegister() {
		$form = new FormRegister();
		$form->load($this->request->post());
		if (!($model = $form->execute())) {
			return $this->asJson([
				'success' => false,
				'error' => $form->getFirstError(),
				'errors' => $form->getErrors(),
			]);
		}
		return $this->asJson(['success' => true, '__id' => $model->primaryKey, 'data' => $this->_prepareItem($model)]);
	}

}