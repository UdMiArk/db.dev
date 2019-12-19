<?php

use console\components\Migration;

class m191218_131500_rework_resource_archivation extends Migration {
	const TABLE_RESOURCE = '{{%resource}}';

	public function up() {
		$this->addColumn($this::TABLE_RESOURCE, 'archived_at', $this->dateTime()->null());
		$this->addColumn($this::TABLE_RESOURCE, 'archived_by', $this->integerPk());
		$this->alterColumn($this::TABLE_RESOURCE, 'archived', $this->integer()->unsigned()->notNull()->defaultValue(0));
	}

	public function down() {
		$this->alterColumn($this::TABLE_RESOURCE, 'archived', $this->boolean()->null()->defaultValue(false));
		$this->dropColumn($this::TABLE_RESOURCE, 'archived_at');
		$this->dropColumn($this::TABLE_RESOURCE, 'archived_by');
	}
}
