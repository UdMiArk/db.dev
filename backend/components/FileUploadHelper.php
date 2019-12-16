<?php


namespace backend\components;


use common\components\UploadedFile;

class FileUploadHelper {
	public static function getMultipartFormFiles() {
		$post = [];
		if (isset($_FILES) && is_array($_FILES)) {
			foreach ($_FILES as $key => $info) {
				$post[$key] = self::loadFilesRecursive($info['name'], $info['tmp_name'], $info['type'], $info['size'], $info['error']);
			}
		}
		return $post;
	}

	/**
	 * Creates UploadedFile instances from $_FILE recursively.
	 * @param string $key key for identifying uploaded file: class name and sub-array indexes
	 * @param mixed $names file names provided by PHP
	 * @param mixed $tempNames temporary file names provided by PHP
	 * @param mixed $types file types provided by PHP
	 * @param mixed $sizes file sizes provided by PHP
	 * @param mixed $errors uploading issues provided by PHP
	 * @return UploadedFile|array|null
	 */
	private static function loadFilesRecursive($names, $tempNames, $types, $sizes, $errors) {
		if (is_array($names)) {
			$result = [];
			foreach ($names as $key => $name) {
				$result[$key] = self::loadFilesRecursive($name, $tempNames[$key], $types[$key], $sizes[$key], $errors[$key]);
			}
		} elseif ((int)$errors !== UPLOAD_ERR_NO_FILE) {
			$result = new UploadedFile([
				'name' => $names,
				'tempName' => $tempNames,
				'type' => $types,
				'size' => $sizes,
				'error' => $errors,
			]);
		} else {
			$result = null;
		}
		return $result;
	}
}