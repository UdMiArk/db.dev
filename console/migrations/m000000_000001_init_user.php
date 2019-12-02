<?php

use console\components\Migration;

class m000000_000001_init_user extends Migration {
	const TABLE_USER = '{{%user}}';

	public function up() {
		$this->createTableWithIntPk($this::TABLE_USER, [
			'created_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
			'updated_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),

			'login' => $this->string()->notNull()->unique(),
			'name' => $this->string()->notNull(),
			'email' => $this->string(255)->notNull(),

			'auth_key' => $this->string(32)->notNull(),
		]);
	}

	public function down() {
		$this->dropTable($this::TABLE_USER);
	}
}
