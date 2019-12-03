<?php

namespace common\components;

use yii\base\Model;

class FormModel extends Model {
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

	public function formName() {
		return '';
	}
}