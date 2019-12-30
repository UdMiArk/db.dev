<?php


namespace common\components\handlers;

use common\components\FileStorageHelper;
use common\components\UploadedFile;
use common\models\Resource;
use yii\base\Exception;

class AttributeFilesHandler extends AttributeHandler {
	public function validate($data, $unprocessed = false, &$errors = null) {
		$newErrors = [];
		$options = $this->getOptions();
		$extensions = @$options['extensions'] ? explode(',', mb_strtolower($options['extensions'])) : null;
		if ($unprocessed) {
			if ($data) foreach ($data as $fileData) {
				if ($fileData instanceof UploadedFile) {
					$fileName = $fileData->name;
					if ($fileData->hasError) {
						$newErrors [] = "'" . $fileName . "': " . $fileData->errorMessage;
					} elseif (!is_file($fileData->tempName)) {
						$newErrors [] = "'" . $fileName . "': Загруженный файл не доступен для чтения";
					} else {
						if ($extensions) {
							$correctExt = false;
							$loweredFileName = mb_strtolower($fileName);
							$nameLen = mb_strlen($loweredFileName);
							foreach ($extensions as $ext) {
								$extLen = mb_strlen($ext);
								if (($extLen < $nameLen) && (mb_substr($loweredFileName, $nameLen - $extLen) === $ext)) {
									$correctExt = true;
									break;
								}
							}
							if (!$correctExt) {
								$newErrors [] = "'" . $fileName . "': Расширение файла не соответсвует разрешенным ('" . implode("', '", $extensions) . "')";
							}
						}
					}
				} else {
					$newErrors[] = "'" . $fileData->name . "': Не удалось прочитать загруженный файл";
				}
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

	public function sanitizeOptions($options, &$errors = []) {
		if (@$options['extensions']) {
			return ['extensions' => $options['extensions']];
		}
		return null;
	}
}