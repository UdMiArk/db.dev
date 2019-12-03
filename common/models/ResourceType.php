<?php

namespace common\models;

use common\components\CommonRecord;

/**
 * ResourceType model
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property boolean $disabled
 *
 * @property-read ResourceTypeAttribute[] $typeAttributes
 */
class ResourceType extends CommonRecord {
	public function attributeLabels() {
		return [
			'name' => "Имя",
			'description' => "Описание",
			'disabled' => "Отключен",
		];
	}

	public function rules() {
		return array_merge(parent::rules(), [
			[['name', 'description'], 'trim', 'on' => [$this::SCENARIO_CREATE, $this::SCENARIO_UPDATE]],
			[['name', 'description'], 'string', 'on' => [$this::SCENARIO_CREATE, $this::SCENARIO_UPDATE]],
			[['name'], 'required', 'on' => [$this::SCENARIO_CREATE, $this::SCENARIO_UPDATE]],
		]);
	}

	public function getTypeAttributes() {
		return $this->hasMany(ResourceTypeAttribute::class, ['resource_type_id' => 'id']);
	}
}
