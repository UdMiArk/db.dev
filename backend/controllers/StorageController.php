<?php


namespace backend\controllers;


use backend\components\BackendController;
use common\components\FileStorageHelper;
use common\data\EArchiveStatus;
use common\models\Resource;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
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
		if ($resource->archived !== EArchiveStatus::NOT_ARCHIVED) {
			throw new ServerErrorHttpException("Ресурс находится в архиве или в процессе архивации");
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

	public function actionResourceArchive($id) {
		$resource = Resource::findOne(['id' => $id]);
		if (!$resource) {
			throw new NotFoundHttpException("Ресурс не найден");
		}
		if (!$resource->archived === EArchiveStatus::ARCHIVED) {
			throw new BadRequestHttpException("Ресурс не в архиве");
		}
		$bucket = FileStorageHelper::getProductBucket($resource->product);
		$fileName = FileStorageHelper::getResourceArchiveName($resource);
		if (!$bucket->fileExists($fileName)) {
			throw new Exception("Потеряна архивированное хранилище ресурса #{$resource->primaryKey} '{$resource->name}'");
		}
		$this->asXSendFile($bucket, $fileName);
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