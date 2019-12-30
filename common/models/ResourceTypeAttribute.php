<?php

namespace common\models;

use common\components\CommonRecord;
use common\components\handlers\AttributeFileHandler;
use common\components\handlers\AttributeFilesHandler;
use common\components\handlers\AttributeHandler;
use common\data\EAttributeType;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * ResourceTypeAttribute model
 *
 * @property integer $id
 * @property integer $resource_type_id
 * @property integer $type
 * @property string $name
 * @property string $description
 * @property boolean $required
 * @property array $options
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
			'options' => "Параметры",
			'description' => "Описание",
			'required' => "Обязателен",
		];
	}

	public function rules() {
		$formScenarios = [$this::SCENARIO_CREATE, $this::SCENARIO_UPDATE];
		return array_merge(parent::rules(), [
			[['name', 'description'], 'trim', 'on' => $formScenarios],
			[['name', 'description'], 'string', 'on' => $formScenarios],
			[['required'], 'boolean', 'on' => $formScenarios],
			[['type'], 'integer', 'on' => $formScenarios],
			[['type'], 'in', 'range' => EAttributeType::getValues(), 'on' => $formScenarios],
			[['name', 'type'], 'required', 'on' => $formScenarios],
			[['options'], 'ruleValidateOptions', 'on' => $formScenarios, 'skipOnEmpty' => false],
		]);
	}

	public function getOptions() {
		/** @noinspection PhpUndefinedFieldInspection */
		return $this->options_json ? Json::decode($this->options_json) : [];
	}

	public function setOptions($val) {
		/** @noinspection PhpUndefinedFieldInspection */
		$this->options_json = empty($val) ? null : Json::encode($val);
	}

	public function ruleValidateOptions() {
		if ($this->type && ($this->isAttributeChanged('options_json') || $this->isAttributeChanged('type'))) {
			$newOptions = $this->handler->sanitizeOptions($this->options, $errors);
			if ($newOptions === false) {
				if ($errors) {
					foreach ($errors as $key => $error) {
						if (is_int($key)) {
							$key = 'options';
						} else {
							$key = 'options.' . $key;
						}
						$key = 'options.' . $key;
						if (!is_array($error)) {
							$error = [$error];
						}
						foreach ($error as $err) {
							$this->addError($key, $err);
						}
					}
				} else {
					$this->addError('options', "Не удалось сохранить настройки поля '{$this->name}'");
				}
			} else {
				$this->options = $newOptions;
			}
		}
	}

	public function getHandler() {
		$handlerClass = $this::getFieldTypeHandlers();
		$handlerClass = @$handlerClass[$this->type];
		return $handlerClass ? new $handlerClass($this, $this->options) : null;
	}

	public function getResourceType() {
		return $this->hasOne(ResourceType::class, ['id' => 'resource_type_id']);
	}

	public function getFrontendInfo() {
		return array_merge(parent::getFrontendInfo(), [
			'name' => $this->name,
			'type' => $this->type,
			'required' => boolval($this->required),
			'description' => $this->description,
			'options' => $this->options,
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
