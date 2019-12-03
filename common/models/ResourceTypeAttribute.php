<?php

namespace common\models;

use common\components\CommonRecord;
use common\data\EAttributeType;

/**
 * ResourceTypeAttribute model
 *
 * @property integer $id
 * @property integer $resource_type_id
 * @property integer $type
 * @property string $name
 * @property string $description
 * @property boolean $required
 *
 * @property-read ResourceType $resourceType
 */
class ResourceTypeAttribute extends CommonRecord {
	public function attributeLabels() {
		return [
			'resource_type_id' => "Тип ресурса",
			'type' => "Тип",
			'name' => "Имя",
			'description' => "Описание",
			'required' => "Обязателен",
		];
	}

	public function rules() {
		return array_merge(parent::rules(), [
			[['name', 'description'], 'trim', 'on' => [$this::SCENARIO_CREATE, $this::SCENARIO_UPDATE]],
			[['name', 'description'], 'string', 'on' => [$this::SCENARIO_CREATE, $this::SCENARIO_UPDATE]],
			[['required'], 'boolean', 'on' => [$this::SCENARIO_CREATE, $this::SCENARIO_UPDATE]],
			[['type'], 'integer', 'on' => [$this::SCENARIO_CREATE, $this::SCENARIO_UPDATE]],
			[['type'], 'in', 'range' => EAttributeType::getValues(), 'on' => [$this::SCENARIO_CREATE, $this::SCENARIO_UPDATE]],
			[['name', 'type'], 'required', 'on' => [$this::SCENARIO_CREATE, $this::SCENARIO_UPDATE]],
			[['resource_type_id'], 'integer', 'on' => [$this::SCENARIO_CREATE]],
			[['resource_type_id'], 'required', 'on' => [$this::SCENARIO_CREATE]],
		]);
	}

	public function getResourceType() {
		return $this->hasOne(ResourceType::class, ['id' => 'resource_type_id']);
	}
}
