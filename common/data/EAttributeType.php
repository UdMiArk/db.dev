<?php


namespace common\data;


use common\components\EnumModel;

class EAttributeType extends EnumModel {
	const FILE = 10;
	const FILES = 20;

	protected static function __values__() {
		return [
			static::FILE => "Файл",
			static::FILES => "Набор файлов",
		];
	}
}