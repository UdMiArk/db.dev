<?php

use console\components\Migration;

class m191209_163500_user_sc_auth extends Migration {
	const TABLE_USER = '{{%user}}';

	public function up() {
		$this->addColumn($this::TABLE_USER, 'sc_key', $this->string(32)->null());
	}

	public function down() {
		$this->dropColumn($this::TABLE_USER, 'sc_key');
	}
}
