<?php

use console\components\Migration;

class m191202_124600_init_market_product extends Migration {
	const TABLE_MARKET = '{{%market}}';
	const TABLE_PRODUCT = '{{%product}}';

	public function up() {
		$this->createTableWithIntPk($this::TABLE_MARKET, [
			'name' => $this->string()->notNull(),
			'path' => $this->string()->notNull(),
			'id_ext' => $this->integer()->notNull()->unique(),
		]);
		$this->createTableWithIntPk($this::TABLE_PRODUCT, [
			'name' => $this->string()->notNull(),
			'path' => $this->string()->notNull(),
			'id_ext' => $this->integer()->notNull()->unique(),
			'market_id' => $this->integerPk()->notNull(),
		]);
		$this->addForeignKey(
			'fk-product-market_id',
			$this::TABLE_PRODUCT,
			'market_id',
			$this::TABLE_MARKET,
			'id',
			'RESTRICT',
			'CASCADE',
			);
	}

	public function down() {
		$this->dropForeignKey('fk-product-market_id', $this::TABLE_PRODUCT);
		$this->dropTable($this::TABLE_PRODUCT);
		$this->dropTable($this::TABLE_MARKET);
	}
}
