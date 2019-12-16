<?php

namespace common\models;

use common\components\CommonRecord;
use common\components\handlers\AttributeFileHandler;
use common\components\handlers\AttributeFilesHandler;
use common\components\handlers\AttributeHandler;
use common\data\EAttributeType;
use yii\base\Exception;

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
 * @property-read AttributeHandler $handler
 */
class ResourceTypeAttribute extends CommonRecord {
	public static function getFieldTypeHandlers() {
		return [
			EAttributeType::FILE => AttributeFileHandler::class,
			EAttributeType::FILES => AttributeFilesHandler::class,
		];
	}

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

	public function getHandler() {
		$handlerClass = $this::getFieldTypeHandlers();
		$handlerClass = @$handlerClass[$this->type];
		return $handlerClass ? new $handlerClass($this) : null;
	}

	public function getResourceType() {
		return $this->hasOne(ResourceType::class, ['id' => 'resource_type_id']);
	}

	public function getFrontendInfo() {
		return array_merge(parent::getFrontendInfo(), [
			'name' => $this->name,
			'type' => $this->type,
			'required' => $this->required,
			'description' => $this->description,
		]);
	}

	/**
	 * @param $data
	 * @param bool $unprocessed
	 * @param array $attrErrors
	 *
	 * @return boolean
	 */
	public function validateResourceData($data, $unprocessed = false, &$attrErrors = []) {
		if (is_null($data)) {
			if ($this->required) {
				$attrErrors [] = "Поле \"" . $this->name . "\" обязательно к заполнению";
				return false;
			}
			return true;
		}
		$handler = $this->handler;
		if (!$handler) {
			$attrErrors [] = "Не удалось определить тип поля";
			return false;
		}
		return $handler->validate($data, $unprocessed, $attrErrors);
	}

	/**
	 * @param $data
	 * @param bool $unprocessed
	 * @return string[]
	 */
	public function getFileNames($data, $unprocessed = false) {
		return $this->handler->getFileNames($data, $unprocessed);
	}

	/**
	 * Process uploaded resource data before storage
	 *
	 * @param mixed $data
	 * @param Resource $resource
	 * @return array
	 * @throws Exception
	 */
	public function processResourceData($data, Resource $resource) {
		$handler = $this->handler;
		if (!$handler) {
			throw new Exception("Failed to initialize attribute handler for attribute '" . $this->name . "' (type '" . $this->type . "')");
		}
		return $handler->process($data, $resource);
	}
}
