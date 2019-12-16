<?php

namespace common\models;

use common\components\CommonRecord;
use common\components\FileStorageHelper;

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

	public static function getAvailable($withAttributes = false) {
		$query = static::find()->andWhere(['disabled' => false])->orderBy(['name' => SORT_ASC]);
		if ($withAttributes) {
			$query->with('typeAttributes');
		}
		return $query->all();
	}

	public function getFrontendInfo($withAttributes = false) {
		$result = array_merge(parent::getFrontendInfo(), [
			'name' => $this->name,
			'description' => $this->description,
		]);
		if ($withAttributes) {
			$attributes = [];
			foreach ($this->typeAttributes as $attribute) {
				$attributes [] = $attribute->getFrontendInfo();
			}
			$result['attributes'] = $attributes;
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
