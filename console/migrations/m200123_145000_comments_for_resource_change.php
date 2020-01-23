<?php

use console\components\Migration;

class m200123_145000_comments_for_resource_change extends Migration {
	const TABLE_RESOURCE = '{{%resource}}';

	public function up() {
		$this->addColumn($this::TABLE_RESOURCE, 'status_comment', $this->text()->null());
		$this->addColumn($this::TABLE_RESOURCE, 'archived_comment', $this->text()->null());
	}

	public function down() {
		$this->dropColumn($this::TABLE_RESOURCE, 'archived_comment');
		$this->dropColumn($this::TABLE_RESOURCE, 'status_comment');
	}
}
