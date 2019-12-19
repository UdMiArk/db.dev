<?php

namespace backend\controllers;

use backend\components\BackendController;
use Yii;
use yii\web\NotFoundHttpException;

class SiteController extends BackendController {
	public function behaviors() {
		$result = parent::behaviors();
		array_unshift($result['access']['rules'], [
			'actions' => ['error', 'index'],
			'allow' => true,
		]);
		return $result;
	}

	public function actionError() {
		if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
			$exception = new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
		}

		return $this->asJson(['error' => [
			'message' => $exception->getMessage(),
			'code' => $exception->getCode(),
			//'name' => $exception->getName(),
			'route' => Yii::$app->requestedRoute,
		]]);
	}

	public function actionIndex() {
		return $this->asJson([
			'app' => 'backend',
		]);
	}

	public function actionQueueStatus($id) {
		return $this->asJson([
			'status' => $this->queue->status($id),
		]);
	}

	public function actionInfo() {
		phpinfo();
	}
}
