<?php

namespace common\models;

use common\components\CommonRecord;

/**
 * Product model
 *
 * @property integer $id
 * @property integer $id_ext
 * @property integer $market_id
 * @property string $name
 * @property string $path
 *
 * @property-read Market $market
 */
class Product extends CommonRecord {
	public function attributeLabels() {
		return [
			'id_ext' => "ID",
			'name' => "Имя",
			'path' => "Путь",
			'market_id' => "Рынок",
		];
	}

	public function getMarket() {
		return $this->hasOne(Market::class, ['id' => 'market_id']);
	}
}
