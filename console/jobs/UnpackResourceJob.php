<?php


namespace console\jobs;


use common\components\FileStorageHelper;
use common\data\EArchiveStatus;
use common\models\Resource;
use yii\base\BaseObject;
use yii\console\Exception;
use yii\queue\RetryableJobInterface;

class UnpackResourceJob extends BaseObject implements RetryableJobInterface {
	public $resource_id;

	public function canRetry($attempt, $error) {
		return false;
	}

	public function getTtr() {
		return 3600;
	}

	/**
	 * @param \yii\queue\Queue $queue
	 * @return mixed|void
	 * @throws Exception
	 */
	public function execute($queue) {
		$resource = $this->getResource();
		$this->validateResource($resource);
		try {
			$resource->archived = EArchiveStatus::NOT_ARCHIVED;
			$resource->archived_at = null;
			$resource->archived_by = null;
			FileStorageHelper::unpackResourceBucket($resource, function () use ($resource) {
				if (!$resource->save()) {
					throw new Exception("Не удалось сохранить ресурс после распаковки: " . $resource->getFirstError());
				}
			});
		} catch (\Throwable $e) {
			$resource->archived = EArchiveStatus::ARCHIVED;
			$resource->update();
			throw $e;
		}
	}

	public function getResource() {
		$resource = Resource::findOne($this->resource_id);
		if (!$resource) {
			throw new Exception("Не удалось найти ресурс: '" . $this->resource_id . "'");
		}
		return $resource;
	}

	public function validateResource(Resource $resource) {
		switch ($resource->archived) {
			case EArchiveStatus::NOT_ARCHIVED:
				throw new Exception("Ресурс '" . $resource->primaryKey . "' не находится в архиве");
			case EArchiveStatus::AWAITING_DEARCHIVATION:
				break;
			default:
				throw new Exception("Ресурс '" . $resource->primaryKey . "' не отмечен как ожидающий распаковки");
		}
	}
}