<?php

namespace common\models;

use common\components\CommonRecord;
use common\components\FileStorageHelper;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\db\AfterSaveEvent;

/**
 * ResourceType model
 *
 * @property integer $id
 * @property string $name
 * @property string $group_name
 * @property string $description
 * @property string $description_link
 * @property boolean $disabled
 * @property integer $responsible_id
 *
 * @property ResourceTypeAttribute[] $typeAttributes
 * @property User $responsible
 * @property-read Resource[] $resources
 */
class ResourceType extends CommonRecord {
	const RBAC_MANAGE = 'app.resourcetype.manage';

	/* @var ResourceTypeAttribute[] */
	private $_newTypeAttributes = false;

	public function attributeLabels() {
		return [
			'name' => "Имя",
			'group_name' => "Группа",
			'description' => "Описание",
			'description_link' => "Ссылка на описание",
			'disabled' => "Отключен",
			'typeAttributes' => 'Поля ресурса',
			'responsible_id' => "Ответственный по умолчанию",
		];
	}

	public function rules() {
		$formScenarios = [$this::SCENARIO_CREATE, $this::SCENARIO_UPDATE];
		return array_merge(parent::rules(), [
			[['name', 'description', 'description_link', 'group_name'], 'trim', 'on' => $formScenarios],
			[['name', 'description', 'description_link', 'group_name'], 'string', 'on' => $formScenarios],
			[['name'], 'required', 'on' => $formScenarios],
			[['responsible_id'], 'exist', 'targetRelation' => 'responsible', 'on' => $formScenarios],
			[['typeAttributes'], 'ruleValidateTypeAttributes', 'skipOnEmpty' => false, 'on' => $formScenarios],
		]);
	}

	public function ruleValidateTypeAttributes() {
		if ($this->_newTypeAttributes !== false) {
			if (!$this->_newTypeAttributes) {
				$this->addError('typeAttributes', "Для типа ресурса должно быть определено как минимум одно поле");
			} else {
				foreach ($this->_newTypeAttributes as $idx => $attribute) {
					$attrKey = 'typeAttributes.' . $idx;
					if (!$attribute->validate()) {
						$errors = $attribute->getErrors();
						foreach ($errors as $key => $propErrors) {
							$key = $attrKey . '.' . $key;
							foreach ($propErrors as $err) {
								$this->addError($key, $err);
							}
						}
					}
				}
			}
		}
	}

	public function init() {
		parent::init();
		$this->on($this::EVENT_AFTER_INSERT, [$this, '_onAfterSave']);
		$this->on($this::EVENT_AFTER_UPDATE, [$this, '_onAfterSave']);
	}

	/**
	 * @param AfterSaveEvent $ev
	 * @throws Exception
	 */
	protected function _onAfterSave() {
		if ($this->_newTypeAttributes) {
			$newIds = [];
			foreach ($this->_newTypeAttributes as $attrModel) {
				$attrModel->resource_type_id = $this->primaryKey;
				if (!$attrModel->save()) {
					$errName = $attrModel->getFirstError();
					throw new Exception("Ошибка сохранения поля '{$attrModel->name}'" . ($errName ? ': ' . $errName : ''));
				}
				$newIds [] = $attrModel->primaryKey;
			}
			ResourceTypeAttribute::deleteAll(['and', ['resource_type_id' => $this->primaryKey], ['not', ['id' => $newIds]]]);
			$this->populateRelation('typeAttributes', $this->_newTypeAttributes);
			$this->_newTypeAttributes = false;
		}
	}

	public function getTypeAttributes() {
		if ($this->_newTypeAttributes !== false) {
			return $this->_newTypeAttributes;
		}
		return $this->hasMany(ResourceTypeAttribute::class, ['resource_type_id' => 'id']);
	}

	public function setTypeAttributes($val) {
		$result = [];
		if ($val) {
			foreach ($val as $idx => $item) {
				if (is_array($item)) {
					if (@$item['__id']) {
						if ($this->isNewRecord) {
							throw new InvalidArgumentException("Запрещено ссылаться на поля других типов");
						}
						$itemModel = ResourceTypeAttribute::findOne([
							'id' => $item['__id'],
							'resource_type_id' => $this->primaryKey,
						]);
						if (!$itemModel) {
							throw new InvalidArgumentException("Не найдено поле с id {$item['__id']}");
						}
						$itemModel->setScenario($itemModel::SCENARIO_UPDATE);
					} else {
						$itemModel = new ResourceTypeAttribute();
						$itemModel->setScenario($itemModel::SCENARIO_CREATE);
					}
					$itemModel->resource_type_id = $this->primaryKey;
					$itemModel->setAttributes($item);
					$result [@$item['__key'] ?? ('_' . $idx)] = $itemModel;
				} elseif ($item instanceof ResourceTypeAttribute) {
					if ($item->primaryKey !== $item->resource_type_id) {
						throw new InvalidArgumentException("Запрещено ссылаться на поля других типов");
					}
					$result ['_' . $idx] = $item;
				}
			}
		}
		$this->_newTypeAttributes = $result;
		if ($this->isRelationPopulated('typeAttributes')) {
			unset($this->typeAttributes);
		}
	}

	public function getResponsible() {
		return $this->hasOne(User::class, ['id' => 'responsible_id']);
	}

	public function setResponsible($val) {
		if (is_array($val)) {
			$model = User::findOne(['id' => @$val['__id']]);
			if (!$model) {
				throw new InvalidArgumentException("Не удалось определить пользователя");
			}
			$val = $model;
			unset($model);
		}
		if (!(is_null($val) || $val instanceof User)) {
			throw new InvalidArgumentException("Недопустимое значение для поля 'Ответственный'");
		}
		if (is_null($val)) {
			unset($this->responsible);
			$this->responsible_id = null;
		} else {
			if ($val->isNewRecord) {
				throw new InvalidArgumentException("Объект для поля 'Ответственный' должен быть сохранен");
			}
			$this->populateRelation('responsible', $val);
			$this->responsible_id = $val->primaryKey;
		}
	}

	public function getResources() {
		return $this->hasMany(Resource::class, ['type_id' => 'id']);
	}

	public static function getAvailable($withAttributes = false) {
		$query = static::find()->andWhere(['disabled' => false])->orderBy(['name' => SORT_ASC]);
		if ($withAttributes) {
			$query->with('typeAttributes');
		}
		return $query->all();
	}

	public static function getVisible($withAttributes = false) {
		$query = static::find()->alias('type')->orderBy(['name' => SORT_ASC]);
		$query->leftJoin(['resources' => Resource::find()->distinct()->select(['type_id'])], '`type`.`id` = `resources`.`type_id`');
		$query->andWhere(['or', ['disabled' => false], '`resources`.`type_id` IS NOT NULL']);
		if ($withAttributes) {
			$query->with('typeAttributes');
		}
		return $query->all();
	}

	public function getFrontendInfo($withAttributes = false) {
		$result = array_merge(parent::getFrontendInfo(), [
			'name' => $this->name,
			'group_name' => $this->group_name,
			'description' => $this->description,
			'description_link' => $this->description_link,
			'responsible_id' => $this->responsible_id,
			'responsible' => $this->responsible_id ? $this->responsible->getFrontendInfo() : null,
		]);
		if ($withAttributes) {
			$attributes = [];
			foreach ($this->typeAttributes as $attribute) {
				$attributes [] = $attribute->getFrontendInfo();
			}
			$result['typeAttributes'] = $attributes;
		}
		return $result;
	}

	public function getRightsData() {
		$result = [];

		$user = \Yii::$app->user;
		if ($user->can($this::RBAC_MANAGE, ['target' => $this])) {
			$result['canChange'] = true;
			if (!$this->getResources()->exists()) {
				$result['canChangeFields'] = true;
				$result['canDelete'] = true;
			}
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @param bool $unprocessed
	 * @param array $errors
	 * @return bool
	 */
	public function validateResourceData($data, $unprocessed = false, &$errors = []) {
		$foundErrors = false;

		$fileNames = [FileStorageHelper::META_FILE_NAME => "Системные"];
		foreach ($this->typeAttributes as $attribute) {
			$attrErrors = [];
			$attrData = @$data[$attribute->primaryKey];
			if (!$attribute->validateResourceData($attrData, $unprocessed, $attrErrors)) {
				$foundErrors = true;
				if (empty($attrErrors)) {
					$attrErrors = "Недопустимое значение";
				}
			} else if (!is_null($attrData)) {
				$attrFileNames = $attribute->getFileNames($attrData, $unprocessed);
				if ($attrFileNames) foreach ($attrFileNames as $fileName) {
					if (array_key_exists($fileName, $fileNames)) {
						$foundErrors = true;
						$attrErrors [] = "Имя файла '" . $fileName . "' уже занято файлом поля '" . $fileNames[$fileName] . "'";
					} else {
						$fileNames[$fileName] = $attribute->name;
					}
				}
			}
			if ($attrErrors) {
				$errors[$attribute->primaryKey] = $attrErrors;
			}
		}

		return !$foundErrors;
	}

	/**
	 * @param $data
	 * @param Resource $resource
	 * @return array
	 * @throws \yii\base\Exception
	 */
	public function processResourceData($data, Resource $resource) {
		$newData = [];
		foreach ($this->typeAttributes as $attribute) {
			$newData[$attribute->primaryKey] = $attribute->processResourceData(@$data[$attribute->primaryKey], $resource);
		}
		return $newData;
	}
}
