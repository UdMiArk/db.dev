<?php


namespace backend\controllers;


use backend\components\BackendController;
use backend\components\ListRequestProcessor;
use common\data\RBACData;
use common\models\Resource;
use common\models\ResourceType;
use common\models\User;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class ResourceTypesController extends BackendController {
	public $authFullPermissions = ResourceType::RBAC_MANAGE;

	public function behaviors() {
		return ArrayHelper::merge(parent::behaviors(), [
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'view' => ['GET'],
					'list' => ['GET'],
					'roles' => ['GET'],
					'responsible' => ['GET'],

					'*' => ['POST'],
				],
			],
		]);
	}

	public function actionList() {
		$listProcessor = new ListRequestProcessor();

		$listProcessor->sortingHandlers = [
			'responsible' => function ($query, $dir) {
				/* @var ActiveQuery $query */
				$query->orderBy(['responsible.name' => $dir]);
			},
		];

		$listProcessor->filterHandlers = [
			'enabled' => function (Query $query, $value) {
				$query->andWhere(['rtype.disabled' => !$value]);
			},
			'name' => function (Query $query, $value) {
				$query->andWhere(['like', 'rtype.name', $value]);
			},
		];

		$query = ResourceType::find()->alias('rtype')->joinWith('responsible responsible')->orderBy(['rtype.id' => SORT_DESC]);

		$listProcessor->apply($query);

		return $this->asJson($listProcessor->prepare($query, [
			'__id' => 'primaryKey',
			'name',
			'description',
			'disabled',
			'responsible' => function ($item) {
				/* @var User $responsible */
				$responsible = $item->responsible;
				return $responsible ? $responsible->getFrontendInfo() : null;
			},
		]));
	}

	public function actionView($id) {
		return $this->asJson($this->_prepareItem($this->_getRequestItem($id)));
	}

	public function _prepareItem(ResourceType $type) {
		return $type->getFrontendInfo(true);
	}

	/**
	 * @param string|integer $id
	 * @return ResourceType
	 * @throws NotFoundHttpException
	 */
	protected function _getRequestItem($id) {
		$model = ResourceType::findOne($id);
		if (!$model) {
			throw new NotFoundHttpException("Тип не найден");
		}
		return $model;
	}

	public function actionCreate() {
		$model = new ResourceType();
		$model->setScenario($model::SCENARIO_CREATE);
		$model->load($this->request->post());
		if (!$model->save()) {
			return $this->asJson([
				'success' => false,
				'error' => $model->getFirstError(),
				'errors' => $model->getErrors(),
			]);
		}
		return $this->asJson(['success' => true, '__id' => $model->primaryKey, 'data' => $this->_prepareItem($model)]);
	}

	public function actionUpdate($id) {
		$model = $this->_getRequestItem($id);
		$post = $this->request->post();
		if (@$post['typeAttributes'] && $model->getResources()->exists()) {
			throw new BadRequestHttpException(
				"Поля этого типа нельзя редактировать так как существуют связанные ресурсы"
			);
		}
		$model->setScenario($model::SCENARIO_UPDATE);
		if ($model->load($post) && !$model->save()) {
			return $this->asJson([
				'success' => false,
				'error' => $model->getFirstError(),
				'errors' => $model->getErrors(),
			]);
		}
		return $this->asJson(['success' => true, '__id' => $model->primaryKey, 'data' => $this->_prepareItem($model)]);
	}

	public function actionDelete($id) {
		$model = $this->_getRequestItem($id);
		if ($model->getResources()->exists()) {
			throw new BadRequestHttpException(
				"Тип удалить нельзя, с ним связаны существующие ресурсы"
			);
		}
		if (!$model->delete()) {
			$error = $model->getFirstError();
			throw new BadRequestHttpException("Не удалось удалить тип" . ($error ? ': ' . $error : ''));
		}
		return $this->asJson([
			'success' => true,
		]);
	}

	public function actionResponsible() {
		$auth = \Yii::$app->authManager;
		$admins = $auth->getUserIdsByRole(RBACData::ROLE_SUPERUSER);
		$moderators = $auth->getUserIdsByRole(Resource::RBAC_ROLE_MODERATOR);
		$users = User::find()->andWhere(['id' => array_merge($admins, $moderators)])->orderBy(['name' => SORT_ASC])->all();
		$result = [];
		foreach ($users as $user) {
			/* @var User $user */
			$result [] = $user->getFrontendInfo();
		}
		return $this->asJson($result);
	}
}