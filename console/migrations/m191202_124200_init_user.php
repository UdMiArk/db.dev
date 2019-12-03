<?php

use console\components\Migration;

class m191202_124200_init_user extends Migration {
	const TABLE_USER = '{{%user}}';

	public function up() {
		$this->createTableWithIntPk($this::TABLE_USER, [
			'created_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
			'updated_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),

			'domain' => $this->string()->notNull(),
			'login' => $this->string()->notNull(),
			'name' => $this->string()->notNull(),
			'email' => $this->string(255)->notNull(),

			'status' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),

			'auth_key' => $this->string(32)->notNull(),
		]);
		$this->createIndex('unq-user-ldap_login', $this::TABLE_USER, ['domain', 'login'], true);
	}

	public function down() {
		$this->dropTable($this::TABLE_USER);
	}
}
