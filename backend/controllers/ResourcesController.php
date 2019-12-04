<?php


namespace backend\controllers;


use backend\components\BackendController;
use backend\components\ListRequestProcessor;
use common\components\ProductAccessibilityHelper;
use common\data\EResourceStatus;
use common\models\Market;
use common\models\Product;
use common\models\Resource;
use common\models\ResourceType;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class ResourcesController extends BackendController {
	public function actionMarkets() {
		return $this->asJson(Market::cachedAll());
	}

	public function actionStructure() {
		return $this->asJson(Product::cachedStructure());
	}

	public function actionCreationData() {
		$availableProducts = ProductAccessibilityHelper::getAvailableProducts($this->user);
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
				'availableProducts' => $availableProducts,
				'resourceTypes' => $this->_formatResourceTypes(ResourceType::getAvailable()),
			];
		}
		return $this->asJson($result);
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

	public function actionList() {
		$listProcessor = new ListRequestProcessor();

		$query = Resource::find()
			->alias('resource')
			->innerJoinWith(['user user', 'product product', 'type type'])
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
			'product' => function ($item) {
				return $item->product->getFrontendInfo();
			},
		]));
	}

	public function actionCreate() {
		$item = new Resource();
		$item->user_id = \Yii::$app->user->id;
		$item->setScenario($item::SCENARIO_CREATE);
		$item->load($this->request->post());
		if (!$item->save()) {
			throw new BadRequestHttpException($item->getFirstError());
		}
		return $this->asJson(['success' => true, '__id' => $item->primaryKey]);
	}

	public function actionView($id) {
		return $this->asJson(
			$this->_getRequestItem($id)
				->getFrontendInfo(true, true, true)
		);
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
		if ($item->status === EResourceStatus::CONFIRMED) {
			throw new BadRequestHttpException("Удаление утвержденных ресурсов запрещено");
		}
		$item->setScenario($item::SCENARIO_DELETE);
		if (!$item->delete()) {
			throw new BadRequestHttpException($item->getFirstError());
		}
		return $this->asJson(['success' => true, '__id' => $item->primaryKey]);
	}

}