<?php


namespace common\components;


use yii\db\ActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

class CommonRecord extends ActiveRecord {
	const SCENARIO_CREATE = 'create';
	const SCENARIO_UPDATE = 'update';
	const SCENARIO_DELETE = 'delete';

	public static function tableName() {
		return "{{%" . static::modelName() . "}}";
	}

	public static function modelName() {
		return Inflector::camel2id(StringHelper::basename(static::class));
	}

	public function transactions() {
		// Dirty hack to make all scenarios transactional by default
		$scenario = $this->getScenario();
		return [$scenario => $this::OP_ALL];
	}

	public function getFirstError($attribute = null) {
		if (!is_null($attribute)) {
			return parent::getFirstError($attribute);
		}

		$messages = $this->getFirstErrors();
		if ($messages) {
			return $messages[array_keys($messages)[0]];
		}
		return null;
	}

	public function getFrontendInfo() {
		return [
			'__id' => $this->primaryKey,
		];
	}

	public function formName() {
		return '';
	}
}