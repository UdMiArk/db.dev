<?php

namespace common\models;

use common\components\CommonRecord;
use common\components\FileStorageHelper;

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
	public function init() {
		parent::init();
		$this->on($this::EVENT_AFTER_INSERT, [$this, 'evHandleInsert']);
	}

	public function generatePath() {
		$result = $this->name;
		if ($this->primaryKey) {
			$result .= ' (id ' . $this->primaryKey . ')';
		}
		return '/' . FileStorageHelper::preparePath($result);
	}

	protected function evHandleInsert() {
		$newPath = $this->generatePath();
		if ($this->path !== $newPath) {
			$this->path = $newPath;
			$this->setOldAttribute('path', $newPath);
			$this::updateAll(['path' => $newPath], ['id' => $this->primaryKey]);
		}
	}

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

	public static function cachedStructure(User $user = null) {
		$productsByMarket = [];
		$prodQuery = static::find()->alias('product')
			->select(['__id' => 'id', 'id_ext', 'name', 'market_id'])
			->orderBy(['name' => SORT_ASC]);
		if ($user) {
			$prodQuery->innerJoin(
				[
					'_flt_user' => Resource::find()
						->andWhere(['user_id' => $user->primaryKey])
						->select(['product_id'])->distinct(),
				],
				'`_flt_user`.`product_id` = `product`.`id`'
			);
		}
		foreach ($prodQuery->asArray()->each(1000) as $row) {
			$row['id'] = 'p' . $row['__id'];
			$productsByMarket[$row['market_id']] [] = $row;
		}
		$markets = Market::cachedAll();
		$result = [];
		foreach ($markets as $market) {
			$marketProducts = @$productsByMarket[$market['__id']];
			if ($marketProducts || !$user) {
				$market['id'] = 'm' . $market['__id'];
				$market['products'] = @$productsByMarket[$market['__id']];
				$result[] = $market;
			}
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
