<?php

use console\components\Migration;

class m191219_165000_rework_resource_approval extends Migration {
	const TABLE_RESOURCE = '{{%resource}}';
	const TABLE_USER = '{{%user}}';

	public function up() {
		$this->addColumn($this::TABLE_RESOURCE, 'status_by', $this->integerPk()->null());

		$this->addForeignKey(
			'fk-resource-status_by',
			$this::TABLE_RESOURCE,
			'status_by',
			$this::TABLE_USER,
			'id',
			'RESTRICT',
			'CASCADE',
			);
	}

	public function down() {
		$this->dropForeignKey('fk-resource-status_by', $this::TABLE_RESOURCE);
		$this->dropColumn($this::TABLE_RESOURCE, 'status_by');
	}
}
