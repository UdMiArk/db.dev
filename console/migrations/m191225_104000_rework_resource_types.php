<?php

use console\components\Migration;

class m191225_104000_rework_resource_types extends Migration {
	const TABLE_USER = '{{%user}}';
	const TABLE_RESOURCE_TYPE = '{{%resource-type}}';
	const TABLE_RESOURCE_TYPE_ATTRIBUTE = '{{%resource-type-attribute}}';

	public function up() {
		$this->addColumn($this::TABLE_RESOURCE_TYPE_ATTRIBUTE, 'options_json', $this->text()->null());
		$this->addColumn($this::TABLE_RESOURCE_TYPE, 'responsible_id', $this->integerPk()->null());

		$this->addForeignKey(
			'fk-restype-responsible_id',
			$this::TABLE_RESOURCE_TYPE,
			'responsible_id',
			$this::TABLE_USER,
			'id',
			'SET NULL',
			'CASCADE',
			);
	}

	public function down() {
		$this->dropForeignKey('fk-restype-responsible_id', $this::TABLE_RESOURCE_TYPE);
		$this->dropColumn($this::TABLE_RESOURCE_TYPE, 'responsible_id');
		$this->dropColumn($this::TABLE_RESOURCE_TYPE_ATTRIBUTE, 'options_json');
	}
}
