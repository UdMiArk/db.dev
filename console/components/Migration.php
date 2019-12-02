<?php


namespace console\components;


class Migration extends \yii\db\Migration {
	protected function createTableWithIntPk($tableName, $columns) {
		return $this->createTable($tableName, array_merge([
			'id' => $this->primaryKey(),
		], $columns));
	}

	protected function integerPk() {
		return $this->integer();
	}
}