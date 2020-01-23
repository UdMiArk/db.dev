<?php


namespace backend\controllers;


use backend\components\BackendController;
use backend\components\FileUploadHelper;
use backend\components\ListRequestProcessor;
use backend\models\FormResourceCreate;
use backend\models\FormScLogin;
use common\components\ProductAccessibilityHelper;
use common\data\EArchiveStatus;
use common\data\EResourceStatus;
use common\data\RBACData;
use common\models\Market;
use common\models\Product;
use common\models\Resource;
use common\models\ResourceType;
use console\jobs\ArchiveResourceJob;
use console\jobs\UnpackResourceJob;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ResourcesController extends BackendController {
	public $authFullPermissions = Resource::RBAC_ALL;

	public function behaviors() {
		$behaviors = ArrayHelper::merge(parent::behaviors(), [
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'view' => ['GET'],
					'list' => ['GET'],
					'markets' => ['GET'],
					'structure' => ['GET'],
					'creation-data' => ['GET'],
					'existing-types' => ['GET'],

					'test' => ['GET'],

					'*' => ['POST'],
				],
			],
		]);
		$behaviors['access']['rules'] = array_merge([
			[
				'actions' => ['view', 'markets', 'structure', 'list', 'existing-types', 'test'],
				'permissions' => [Resource::RBAC_VIEW],
				'allow' => true,
			],
			[
				'actions' => ['create', 'creation-data', 'sc-login'],
				'permissions' => [Resource::RBAC_CREATE],
				'allow' => true,
			],
			[
				'actions' => ['update'],
				'permissions' => [Resource::RBAC_UPDATE],
				'allow' => true,
			],
			[
				'actions' => ['delete'],
				'permissions' => [Resource::RBAC_DELETE, Resource::RBAC_CREATE],
				'allow' => true,
			],
			[
				'actions' => ['set-status'],
				'permissions' => [Resource::RBAC_APPROVE],
				'allow' => true,
			],
			[
				'actions' => ['archive', 'dearchive'],
				'permissions' => [Resource::RBAC_APPROVE],
				'allow' => true,
			],
		], $behaviors['access']['rules']);

		return $behaviors;
	}

	public function actionTest() {
		return $this->asJson(\Yii::$app->authManager->checkAccess(null, Resource::RBAC_VIEW));
	}

	public function actionMarkets() {
		return $this->asJson(Market::cachedAll());
	}

	public function actionStructure($only_mine = false) {
		$only_mine = boolval(intval($only_mine));
		return $this->asJson(Product::cachedStructure($only_mine ? $this->user : null));
	}

	public function actionScLogin() {
		$user = $this->user;
		$model = new FormScLogin();
		$model->load($this->request->post());
		if (!$model->execute($user)) {
			return $this->asJson([
				'success' => false,
				'error' => $model->getFirstError(),
				'errors' => $model->getErrors(),
			]);
		}
		return $this->asJson([
			'success' => true,
			'data' => $this->_prepareCreationData($user),
		]);
	}

	public function actionCreationData() {
		return $this->asJson($this->_prepareCreationData($this->user));
	}

	public function actionExistingTypes() {
		return $this->asJson($this->_formatResourceTypes(ResourceType::getVisible()));
	}

	public function actionList() {
		$listProcessor = new ListRequestProcessor();

		$listProcessor->sortingHandlers = [
			'product' => function ($query, $dir) {
				/* @var \yii\db\Query $query */
				$query->orderBy(['product.name' => $dir]);
			},
		];
		$listProcessor->defaultFilters = [
			'show_archived' => 0,
			'approved_only' => 1,
		];
		$listProcessor->filterHandlers = [
			'product' => function ($query, $value) {
				/* @var \yii\db\Query $query */
				$query->andWhere(['like', 'product.name', $value]);
			},
			'user' => function ($query, $value) {
				/* @var \yii\db\Query $query */
				$query->andWhere(['or', ['like', 'user.name', $value], ['like', 'user.email', $value]]);
			},
			'type' => function ($query, $value) {
				/* @var \yii\db\Query $query */
				$query->andWhere(['like', 'type.name', $value]);
			},
			'product_id' => function ($query, $value) {
				/* @var \yii\db\Query $query */
				$query->andWhere(['resource.product_id' => $value]);
			},
			'market_id' => function ($query, $value) {
				/* @var \yii\db\Query $query */
				$query->andWhere(['product.market_id' => $value]);
			},
			'own_only' => function ($query, $value) {
				if ($value) {
					/* @var \yii\db\Query $query */
					$query->andWhere(['resource.user_id' => \Yii::$app->user->id]);
				}
			},
			'show_archived' => function ($query, $value) {
				if (!$value) {
					/* @var \yii\db\Query $query */
					$query->andWhere(['resource.archived' => EArchiveStatus::NOT_ARCHIVED]);
				}
			},
			'approved_only' => function ($query, $value) {
				if ($value) {
					/* @var \yii\db\Query $query */
					$query->andWhere(['resource.status' => EResourceStatus::APPROVED]);
				}
			},
		];

		$query = Resource::find()
			->alias('resource')
			->innerJoinWith(['user user', 'product product', 'type type'])
			->with('statusBy')
			->orderBy(['resource.created_at' => SORT_DESC]);

		$listProcessor->apply($query);

		return $this->asJson($listProcessor->prepare($query, [
			'__id' => 'primaryKey',
			'created_at',
			'status_at',
			'name',
			'status',
			'archived',
			'type' => function ($item) {
				return $item->type->getFrontendInfo();
			},
			'user' => function ($item) {
				return $item->user->getFrontendInfo();
			},
			'status_by' => function ($item) {
				/* @var $item Resource */
				$user = $item->statusBy;
				return $user ? $user->getFrontendInfo() : null;
			},
			'product' => function ($item) {
				return $item->product->getFrontendInfo();
			},
		]));
	}

	public function actionCreate() {
		$form = new FormResourceCreate();
		$form->load(ArrayHelper::merge($this->request->post(), FileUploadHelper::getMultipartFormFiles()));
		if (!($model = $form->save())) {
			return $this->asJson([
				'success' => false,
				'error' => $form->getFirstError(),
				'errors' => $form->getErrors(),
			]);
		}
		return $this->asJson(['success' => true, '__id' => $model->primaryKey, 'resource' => $this->_prepareResourceData($model)]);
	}

	public function actionView($id) {
		return $this->asJson($this->_prepareResourceData($this->_getRequestItem($id)));
	}

	public function actionUpdate($id) {
		$item = $this->_getRequestItem($id);
		if ($item->status !== EResourceStatus::AWAITING) {
			throw new BadRequestHttpException("Редактирование рассмотренных ресурсов запрещено");
		}
		$item->setScenario($item::SCENARIO_UPDATE);
		if ($item->load($this->request->post())) {
			if (!$item->save()) {
				throw new BadRequestHttpException($item->getFirstError());
			}
		}
		return $this->asJson(['success' => true, '__id' => $item->primaryKey]);
	}

	public function actionDelete($id) {
		$item = $this->_getRequestItem($id);
		$user = \Yii::$app->user;
		if ($item->status === EResourceStatus::APPROVED && !$user->can(RBACData::ROLE_SUPERUSER)) {
			throw new BadRequestHttpException("Удаление утвержденных ресурсов запрещено");
		}
		if ($item->isInArchivationProcess()) {
			throw new BadRequestHttpException("Удаление ресурсов в просессе архивации запрещено");
		}
		// TODO: Move delete access logic to rbac rule
		if ($user->id !== $item->user_id && !$user->can($item::RBAC_APPROVE, ['target' => $item])) {
			throw new ForbiddenHttpException("У вас нет прав на удаление этого ресурса");
		}

		$item->setScenario($item::SCENARIO_DELETE);
		if (!$item->delete()) {
			throw new BadRequestHttpException($item->getFirstError());
		}
		return $this->asJson(['success' => true, '__id' => $item->primaryKey]);
	}

	public function actionSetStatus($id) {
		$approved = !!$this->request->post('approved');
		$comment = trim($this->request->post('comment'));
		$item = $this->_getRequestItem($id);
		if ($item->status !== EResourceStatus::AWAITING) {
			throw new BadRequestHttpException("Статус ресурса уже выставлен");
		}
		$item->status = $approved ? EResourceStatus::APPROVED : EResourceStatus::REJECTED;
		$item->status_by = $this->user->primaryKey;
		$item->status_comment = empty($comment) ? null : $comment;
		if (!$item->update()) {
			throw new BadRequestHttpException($item->getFirstError());
		}
		return $this->asJson(['success' => true, 'resource' => $this->_prepareResourceData($item)]);
	}

	public function actionArchive($id) {
		$resource = $this->_getRequestItem($id);
		$user = $this->user;
		if ($resource->archived !== EArchiveStatus::NOT_ARCHIVED) {
			throw new BadRequestHttpException("Ресурс уже находится в архиве или на пути в него");
		}
		$comment = trim($this->request->post('comment'));
		$resource->archived = EArchiveStatus::AWAITING_ARCHIVATION;
		$resource->archived_by = $user->primaryKey;
		$resource->archived_at = date('Y-m-d H:i:s', time());
		$resource->archived_comment = empty($comment) ? null : $comment;
		$trans = \Yii::$app->db->beginTransaction();
		try {
			$queueId = $this->getQueue()->push(new ArchiveResourceJob([
				'resource_id' => $resource->primaryKey,
			]));
			$resource->archived_queue = $queueId;
			if (!$resource->update()) {
				throw new BadRequestHttpException($resource->getFirstError());
			}
			$trans->commit();
		} catch (\Throwable $e) {
			$trans->rollBack();
			throw $e;
		}
		return $this->asJson([
			'success' => true,
			'queue_id' => $queueId,
			'resource' => $this->_prepareResourceData($resource),
		]);
	}

	public function actionUnpack($id) {
		$resource = $this->_getRequestItem($id);
		if ($resource->archived !== EArchiveStatus::ARCHIVED) {
			throw new BadRequestHttpException("Ресурс не находится в архиве");
		}
		$resource->archived = EArchiveStatus::AWAITING_DEARCHIVATION;
		$trans = \Yii::$app->db->beginTransaction();
		try {
			$queueId = $this->getQueue()->push(new UnpackResourceJob([
				'resource_id' => $resource->primaryKey,
			]));
			$resource->archived_queue = $queueId;
			if (!$resource->update()) {
				throw new BadRequestHttpException($resource->getFirstError());
			}
			$trans->commit();
		} catch (\Throwable $e) {
			$trans->rollBack();
			throw $e;
		}
		return $this->asJson([
			'success' => true,
			'queue_id' => $queueId,
			'resource' => $this->_prepareResourceData($resource),
		]);
	}

	protected function _prepareCreationData($user) {
		$availableProducts = ProductAccessibilityHelper::getAvailableProducts($user);
		if ($availableProducts === false) {
			$result = [
				'needScAuth' => true,
			];
		} elseif (empty($availableProducts)) {
			$result = [
				'noProductsAvailable' => true,
			];
		} else {
			$result = [
				'markets' => Market::cachedAll(),
				'products' => $availableProducts,
				'resourceTypes' => $this->_formatResourceTypes(ResourceType::getAvailable(true), true),
			];
		}
		return $result;
	}

	/**
	 * @param string|integer $id
	 * @return Resource
	 * @throws NotFoundHttpException
	 */
	protected function _getRequestItem($id) {
		$model = Resource::findOne($id);
		if (!$model) {
			throw new NotFoundHttpException("Заявка не найдена");
		}
		return $model;
	}

	/**
	 * @param ResourceType[] $types
	 * @param bool $withAttributes
	 * @return array
	 */
	protected function _formatResourceTypes($types, $withAttributes = false) {
		$result = [];

		foreach ($types as $type) {
			$result [] = $type->getFrontendInfo($withAttributes);
		}

		return $result;
	}

	protected function _prepareResourceData(Resource $resource) {
		$result = $resource->getFrontendInfo(true, true, true);
		$result = array_merge($result, $resource->getRightsData());
		return $result;
	}
}