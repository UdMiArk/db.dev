<?php

namespace common\models;

use common\components\CommonRecord;
use common\components\DateTimeStampBehavior;
use yii\helpers\Json;

/**
 * Resource model
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $product_id
 * @property integer $type_id
 *
 * @property string $created_at
 * @property string $updated_at
 * @property string $status_at
 *
 * @property array $data
 * @property string $data_json
 *
 * @property string $name
 * @property integer $status
 * @property boolean $archived
 * @property string $path
 * @property string $comment
 *
 * @property-read User $user
 * @property-read Product $product
 * @property-read ResourceType $type
 */
class Resource extends CommonRecord {
	public function behaviors() {
		return [
			DateTimeStampBehavior::class,
		];
	}

	public function attributeLabels() {
		return [
			'user_id' => "Создатель",
			'product_id' => "Объект продвижения",
			'type_id' => "Тип ресурса",

			'created_at' => "Дата создания",
			'updated_at' => "Дата последнего изменения",
			'status_at' => "Дата утверждения",

			'data' => "Данные",
			'name' => "Имя",
			'status' => "Статус",
			'archived' => "В архиве",
			'path' => "Путь",
			'comment' => "Комментарий",
		];
	}

	public function rules() {
		return array_merge(parent::rules(), [
			[['name', 'comment'], 'trim', 'on' => [$this::SCENARIO_CREATE]],
			[['name', 'comment'], 'string', 'on' => [$this::SCENARIO_CREATE]],
			[['type_id', 'product_id'], 'integer', 'on' => [$this::SCENARIO_CREATE]],
			[['name', 'type_id', 'product_id'], 'required', 'on' => [$this::SCENARIO_CREATE]],
			[['data'], 'safe', 'on' => [$this::SCENARIO_CREATE]],
		]);
	}

	public function getData() {
		$data = $this->data_json;
		return $data ? Json::decode($data) : [];
	}

	public function setData($val) {
		$this->data_json = empty($val) ? null : Json::encode($val);
	}

	public function getUser() {
		return $this->hasOne(User::class, ['id' => 'user_id']);
	}

	public function getProduct() {
		return $this->hasOne(Product::class, ['id' => 'product_id']);
	}

	public function getType() {
		return $this->hasOne(ResourceType::class, ['id' => 'type_id']);
	}
}
