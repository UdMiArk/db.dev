<?php

namespace common\models;

use common\components\CommonRecord;

/**
 * Market model
 *
 * @property integer $id
 * @property integer $id_ext
 * @property string $name
 * @property string $path
 *
 * @property-read Product[] $products
 */
class Market extends CommonRecord {
	public function attributeLabels() {
		return [
			'id_ext' => "ID",
			'name' => "Имя",
			'path' => "Путь",
		];
	}

	public function getProducts() {
		return $this->hasMany(Product::class, ['market_id' => 'id']);
	}
}
