<?php

use console\components\Migration;

class m191202_133800_init_resource extends Migration {
	const TABLE_PRODUCT = '{{%product}}';
	const TABLE_USER = '{{%user}}';
	const TABLE_RESOURCE = '{{%resource}}';
	const TABLE_RESOURCE_TYPE = '{{%resource-type}}';
	const TABLE_RESOURCE_TYPE_ATTRIBUTE = '{{%resource-type-attribute}}';

	public function up() {
		$this->createTableWithIntPk($this::TABLE_RESOURCE_TYPE, [
			'name' => $this->string()->notNull(),
			'description' => $this->text()->null(),
			'disabled' => $this->boolean()->notNull()->defaultValue(false),
		]);
		$this->createTableWithIntPk($this::TABLE_RESOURCE_TYPE_ATTRIBUTE, [
			'resource_type_id' => $this->integerPk()->notNull(),
			'type' => $this->integer()->unsigned()->notNull(),
			'name' => $this->string()->notNull(),
			'required' => $this->boolean()->notNull()->defaultValue(false),
			'description' => $this->text()->null(),
		]);
		$this->addForeignKey(
			'fk-restype_attribute-resource_type_id',
			$this::TABLE_RESOURCE_TYPE_ATTRIBUTE,
			'resource_type_id',
			$this::TABLE_RESOURCE_TYPE,
			'id',
			'CASCADE',
			'CASCADE',
			);
		$this->createTableWithIntPk($this::TABLE_RESOURCE, [
			'created_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
			'updated_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
			'status_at' => $this->dateTime()->null(),

			'user_id' => $this->integerPk()->notNull(),
			'product_id' => $this->integerPk()->notNull(),
			'type_id' => $this->integerPk()->notNull(),

			'name' => $this->string()->notNull(),
			'data_json' => $this->text()->null(),
			'status' => $this->integer()->unsigned()->notNull()->defaultValue(0),
			'archived' => $this->boolean()->null()->defaultValue(false),
			'path' => $this->string()->notNull(),
			'comment' => $this->text()->null(),
		]);
		$this->addForeignKey(
			'fk-resource-user_id',
			$this::TABLE_RESOURCE,
			'user_id',
			$this::TABLE_USER,
			'id',
			'RESTRICT',
			'CASCADE',
			);
		$this->addForeignKey(
			'fk-resource-product_id',
			$this::TABLE_RESOURCE,
			'product_id',
			$this::TABLE_PRODUCT,
			'id',
			'RESTRICT',
			'CASCADE',
			);
		$this->addForeignKey(
			'fk-resource-type_id',
			$this::TABLE_RESOURCE,
			'type_id',
			$this::TABLE_RESOURCE_TYPE,
			'id',
			'RESTRICT',
			'CASCADE',
			);
	}

	public function down() {
		$this->dropForeignKey('fk-resource-type_id', $this::TABLE_RESOURCE);
		$this->dropForeignKey('fk-resource-product_id', $this::TABLE_RESOURCE);
		$this->dropForeignKey('fk-resource-user_id', $this::TABLE_RESOURCE);
		$this->dropForeignKey('fk-restype_attribute-resource_type_id', $this::TABLE_RESOURCE_TYPE_ATTRIBUTE);
		$this->dropTable($this::TABLE_RESOURCE);
		$this->dropTable($this::TABLE_RESOURCE_TYPE_ATTRIBUTE);
		$this->dropTable($this::TABLE_RESOURCE_TYPE);
	}
}
