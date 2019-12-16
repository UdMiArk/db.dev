<?php


namespace common\components;

/**
 * Class UploadedFile
 * @package common\components
 *
 * @property-read string $errorMessage
 */
class UploadedFile extends \yii\web\UploadedFile {
	public function getErrorMessage() {
		$formatter = \Yii::$app->formatter;
		switch ($this->error) {
			case UPLOAD_ERR_OK:
				return null;
			case UPLOAD_ERR_INI_SIZE:
				return "Размер файла '" . $this->name . "' превышает допустимый сервером: " . $formatter->asSize($this::getMaxFileSize());
			case UPLOAD_ERR_FORM_SIZE:
				return "Суммарный размер загруженных файлов превышает допустимый сервером: " . $formatter->asSize($this::getMaxPostSize());
			case UPLOAD_ERR_PARTIAL:
				return "Файл '" . $this->name . "' не был загружен полностью. Попробуйте ещё раз";
			case UPLOAD_ERR_NO_FILE:
				return "Файл не указан";
			case UPLOAD_ERR_NO_TMP_DIR:
				return "Сервер: Временное хранилище не найдено";
			case UPLOAD_ERR_CANT_WRITE:
				return "Сервер: У сервера нет прав на запись в хранилище";
			case UPLOAD_ERR_EXTENSION:
				return "Загрузка файла '" . $this->name . "' отклонена одним из серверных расширений";
		}
		return "Неизвестная ошибка загрузки файла";
	}

	public static function getMaxFileSize() {
		return static::_convertShorthandIniValue(ini_get('upload_max_filesize'));
	}

	protected static function _convertShorthandIniValue($val) {
		// https://stackoverflow.com/a/58684747
		$pwr = 0;
		if (empty($val))
			return 0;
		$val = mb_strtolower(trim($val));
		if (is_numeric($val))
			return $val;
		/*
		if ($gotISO) {
			$val = str_replace('.', '', $val);
			$val = str_replace(',', '.', $val);
		} else {*/
		$val = str_replace(',', '', $val);
		/*}*/
		$val = str_replace(' ', '', $val);
		if (floatval($val) == 0)
			return 0;
		if (stripos($val, 'k') !== false)
			$pwr = 1;
		elseif (stripos($val, 'm') !== false)
			$pwr = 2;
		elseif (stripos($val, 'g') !== false)
			$pwr = 3;
		elseif (stripos($val, 't') !== false)
			$pwr = 4;
		elseif (stripos($val, 'p') !== false)
			$pwr = 5;
		elseif (stripos($val, 'e') !== false)
			$pwr = 6;
		elseif (stripos($val, 'z') !== false)
			$pwr = 7;
		elseif (stripos($val, 'y') !== false)
			$pwr = 8;
		$val = intval($val);
		$val *= pow(1024, $pwr);
		return $val;
	}

	public static function getMaxPostSize() {
		return static::_convertShorthandIniValue(ini_get('post_max_size'));
	}
}