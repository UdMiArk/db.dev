<?php


namespace common\data;


use common\components\EnumModel;

class EArchiveStatus extends EnumModel {
	const NOT_ARCHIVED = 0;
	const ARCHIVED = 1;
	const AWAITING_ARCHIVATION = 100;
	const AWAITING_DEARCHIVATION = 101;

	protected static function __values__() {
		return [
			static::NOT_ARCHIVED => "Не в архиве",
			static::ARCHIVED => "В архиве",
			static::AWAITING_ARCHIVATION => "Ожидает архивацию",
			static::AWAITING_DEARCHIVATION => "Ожидает распаковки",
		];
	}
}