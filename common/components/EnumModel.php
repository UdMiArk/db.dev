<?php


namespace common\components;


use yii\base\Exception;

class EnumModel {
	public static function getValues() {
		return array_keys(static::__values__());
	}

	protected static function __values__() {
		return [];
	}

	public static function getCaption($value, $default = false, $allowNull = true) {
		$values = static::__values__();
		if (array_key_exists($value, $values)) {
			return $values[$value];
		} elseif ($default !== false) {
			return $default;
		} elseif (is_null($value) && $allowNull) {
			return null;
		} else {
			throw new Exception('Unknown enum key: ' . var_export($value, true));
		}
	}

	public static function getValueByCaption($caption) {
		$values = static::__values__();
		return array_search($caption, $values, true);
	}

	public static function validate($value, $allowNull) {
		$values = static::__values__();
		if (array_key_exists($values, $values)) {
			return $value;
		} elseif (is_null($value) && $allowNull) {
			return null;
		} else {
			throw new Exception('Unknown enum key: ' . var_export($value, true));
		}
	}
}