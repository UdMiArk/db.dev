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

	public static function cachedStructure() {
		$productsByMarket = [];
		foreach (
			static::find()
				->select(['__id' => 'id', 'id_ext', 'name', 'market_id'])
				->orderBy(['name' => SORT_ASC])->asArray()
				->each(1000)
			as $row
		) {
			$row['id'] = 'p' . $row['__id'];
			$productsByMarket[$row['market_id']] [] = $row;
		}
		$result = Market::cachedAll();
		foreach ($result as &$market) {
			$market['id'] = 'm' . $market['__id'];
			$market['products'] = @$productsByMarket[$market['__id']];
		}
		return $result;
	}

	public function getFrontendInfo($withMarket = false) {
		$result = array_merge(parent::getFrontendInfo(), [
			'id_ext' => $this->id_ext,
			'name' => $this->name,
		]);
		if ($withMarket) {
			$result['market'] = $this->market->getFrontendInfo();
		}
		return $result;
	}
}
