<?php


namespace common\components\handlers;

use common\components\FileStorageHelper;
use common\components\UploadedFile;
use common\models\Resource;
use yii\base\Exception;

class AttributeFileHandler extends AttributeHandler {
	public function validate($data, $unprocessed = false, &$errors = null) {
		$newErrors = [];
		$options = $this->getOptions();
		$extensions = @$options['extensions'] ? explode(',', $options['extensions']) : null;
		if ($unprocessed) {
			if ($data instanceof UploadedFile) {
				$fileName = $data->name;
				if ($data->hasError) {
					$newErrors [] = $data->errorMessage;
					return false;
				} elseif (!is_file($data->tempName)) {
					$newErrors [] = "Загруженный файл не доступен для чтения";
					return false;
				} else {
					if ($extensions) {
						$correctExt = false;
						$nameLen = mb_strlen($fileName);
						foreach ($extensions as $ext) {
							$extLen = mb_strlen($ext);
							if (($extLen < $nameLen) && (mb_substr($fileName, $nameLen - $extLen) === $ext)) {
								$correctExt = true;
								break;
							}
						}
						if (!$correctExt) {
							$newErrors [] = "Расширение файла не соответсвует разрешенным ('" . implode("', '", $extensions) . "')";
						}
					}
				}
			} else {
				$newErrors[] = "Не удалось прочитать загруженный файл";
				return false;
			}
		}
		if ($newErrors && is_array($errors)) {
			foreach ($newErrors as $err) {
				$errors [] = $err;
			}
		}
		return empty($newErrors);
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

	public function sanitizeOptions($options, &$errors = []) {
		if (@$options['extensions']) {
			return ['extensions' => $options['extensions']];
		}
		return null;
	}

}