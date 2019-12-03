<?php


namespace common\data;


use common\components\EnumModel;

class EUserStatus extends EnumModel {
	const REGISTERED = 0;
	const DISABLED = 100;

	protected static function __values__() {
		return [
			static::REGISTERED => "Зарегестрирован",
			static::DISABLED => "Отключен",
		];
	}
}