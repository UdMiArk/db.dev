<?php


namespace backend\controllers;


use backend\components\BackendController;
use common\components\FileStorageHelper;
use common\models\Resource;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii2tech\filestorage\BucketInterface;

class StorageController extends BackendController {
	public function actionResourceFile($id, $name, $inline = false) {
		$inline = boolval(intval($inline));
		$resource = Resource::findOne(['id' => $id]);
		if (!$resource) {
			throw new NotFoundHttpException("Ресурс не найден");
		}
		$storeBucket = FileStorageHelper::getResourceBucket($resource);
		if (!$storeBucket->exists()) {
			throw new ServerErrorHttpException("Хранилище файлов ресурса недоступно");
		}
		if (!$storeBucket->fileExists($name)) {
			throw new NotFoundHttpException("Файл не найден");
		}
		return $this->asXSendFile($storeBucket, $name, $inline);
	}

	/**
	 * @param BucketInterface $bucket
	 * @param string $fileName
	 * @param bool $inline
	 * @return \yii\console\Response|\yii\web\Response
	 */
	public function asXSendFile(BucketInterface $bucket, $fileName, $inline = false) {
		$response = \Yii::$app->getResponse();
		$response->headers->set("X-Accel-Redirect", $bucket->getFileUrl($fileName));
		$response->headers->set("Content-Type", 'application/octet-stream');
		$response->headers->set("Content-Disposition", ($inline ? 'inline' : 'attachment') . '; filename="' . str_replace('"', '\\"', $fileName) . '"');
		$response->format = $response::FORMAT_RAW;
		$response->data = "";
		return $response;
	}
}