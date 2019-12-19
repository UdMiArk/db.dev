<?php

use console\components\Migration;

class m191219_164600_stored_resource_queue extends Migration {
	const TABLE_RESOURCE = '{{%resource}}';

	public function up() {
		$this->addColumn($this::TABLE_RESOURCE, 'archived_queue', $this->string(32)->null());
	}

	public function down() {
		$this->dropColumn($this::TABLE_RESOURCE, 'archived_queue');
	}
}
