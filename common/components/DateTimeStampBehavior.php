<?php


namespace common\components;


use DateTimeImmutable;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class DateTimeStampBehavior extends TimestampBehavior {
	public function getValue($event) {
		if ($event->name == ActiveRecord::EVENT_BEFORE_INSERT) {
			$this->preserveNonEmptyValues = true;
		}

		if ($this->value === null) {
			return (new DateTimeImmutable())->format('Y-m-d H:i:s');
		}

		return parent::getValue($event);
	}
}