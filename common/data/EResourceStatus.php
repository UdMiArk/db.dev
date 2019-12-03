<?php


namespace common\data;


use common\components\EnumModel;

class EResourceStatus extends EnumModel {
	const AWAITING = 0;
	const CONFIRMED = 10;
	const REJECTED = 90;

	protected static function __values__() {
		return [
			static::AWAITING => "Ожидание",
			static::CONFIRMED => "Утвержден",
			static::REJECTED => "Отклонен",
		];
	}
}