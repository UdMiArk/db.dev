<?php


namespace common\components\handlers;

use common\components\FileStorageHelper;
use common\components\UploadedFile;
use common\models\Resource;
use yii\base\Exception;

class AttributeFilesHandler extends AttributeHandler {
	public function validate($data, $unprocessed = false, &$errors = []) {
		$foundErrors = false;
		if ($unprocessed) {
			if ($data) foreach ($data as $fileData) {
				if ($fileData instanceof UploadedFile) {
					if ($fileData->hasError) {
						$errors [] = "'" . $fileData->name . "': " . $fileData->errorMessage;
					} elseif (!is_file($fileData->tempName)) {
						$errors [] = "'" . $fileData->name . "': Загруженный файл не деступен для чтения";
					}
				} else {
					$errors[] = "'" . $fileData->name . "': Не удалось прочитать загруженный файл";
				}
			}
		}
		return !$foundErrors;
	}

	public function process($data, Resource $resource) {
		$result = null;
		if ($data) {
			$bucket = FileStorageHelper::getResourceBucket($resource);
			foreach ($data as $fileData) {
				if ($fileData && $fileData instanceof UploadedFile && !$fileData->hasError) {
					if (!$bucket->copyFileIn($fileData->tempName, $fileData->name)) {
						throw new Exception("Не удалось сохранить файл '" . $fileData->name . "' в хранилище");
					}
					$result [] = [
						'name' => $fileData->name,
						'md5' => md5_file($fileData->tempName),
						'size' => $fileData->size,
						'type' => $fileData->type,
					];
				}
			}
		}
		return $result;
	}

	public function getFileNames($data, $unprocessed = false) {
		$result = [];
		if ($data) foreach ($data as $fileData) {
			if ($unprocessed) {
				if ($fileData instanceof UploadedFile) {
					$result [] = $fileData->name;
				}
			} else {
				$result [] = $fileData['name'];
			}
		}
		return $result;
	}
}