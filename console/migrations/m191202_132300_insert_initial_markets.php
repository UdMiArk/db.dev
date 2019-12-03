<?php

use console\components\Migration;

class m191202_132300_insert_initial_markets extends Migration {
	const TABLE_MARKET = '{{%market}}';

	public function safeUp() {
		$this->batchInsert($this::TABLE_MARKET, ['name', 'path', 'id_ext'], [
			['Россия', '/rus', 1],
			['Украина', '/ukr', 2],
			['Европа', '/eur', 3],
		]);
	}

	public function safeDown() {
		$this->delete($this::TABLE_MARKET, ['id_ext' => [1, 2, 3]]);
	}
}
