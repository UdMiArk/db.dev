<?php


namespace common\components\handlers;

use common\components\FileStorageHelper;
use common\components\UploadedFile;
use common\models\Resource;
use yii\base\Exception;

class AttributeFileHandler extends AttributeHandler {
	public function validate($data, $unprocessed = false, &$errors = []) {
		if ($unprocessed) {
			if ($data instanceof UploadedFile) {
				if ($data->hasError) {
					$errors [] = $data->errorMessage;
					return false;
				} elseif (!is_file($data->tempName)) {
					$errors [] = "Загруженный файл не деступен для чтения";
					return false;
				}
			} else {
				$errors[] = "Не удалось прочитать загруженный файл";
				return false;
			}
		}
		return true;
	}

	public function process($data, Resource $resource) {
		$result = null;
		if ($data && $data instanceof UploadedFile && !$data->hasError) {
			$bucket = FileStorageHelper::getResourceBucket($resource);
			if (!$bucket->copyFileIn($data->tempName, $data->name)) {
				throw new Exception("Не удалось сохранить файл '" . $data->name . "' в хранилище");
			}
			$result = [
				'name' => $data->name,
				'md5' => md5_file($data->tempName),
				'size' => $data->size,
				'type' => $data->type,
			];
		}
		return $result;
	}

	public function getFileNames($data, $unprocessed = false) {
		$result = [];
		if (!is_null($data)) {
			if ($unprocessed) {
				if ($data instanceof UploadedFile) {
					$result [] = $data->name;
				}
			} else {
				$result [] = $data['name'];
			}
		}
		return $result;
	}
}