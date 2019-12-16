<?php

use console\components\Migration;

class m191210_141800_insert_initial_resource_types extends Migration {
	const TABLE_RESOURCE_TYPE = '{{%resource-type}}';
	const TABLE_RESOURCE_TYPE_ATTRIBUTE = '{{%resource-type-attribute}}';

	const ATTR_TYPE_FILE = 10;
	const ATTR_TYPE_FILES = 20;

	public function safeUp() {
		$this->insertType("Баннер", [
			['name' => "Основное изображение", 'required' => true],
		], "Тестовый тип (Баннер)");
		$this->insertType("Новость", [
			['name' => "Набранный текст", 'required' => true],
			['name' => "Изображения", 'type' => $this::ATTR_TYPE_FILES],
		], "Тестовый тип (Новость)");
		$this->insertType("Ещё какой-нибудь тип с длинным названием", [
			['name' => "Набранный текст", 'required' => true],
			['name' => "Изображения", 'type' => $this::ATTR_TYPE_FILES],
		], "Тестовый тип (Ещё какой-нибудь)");
	}

	protected function insertType($name, $attrs, $description = null) {
		$this->insert($this::TABLE_RESOURCE_TYPE, ['name' => $name, 'description' => $description]);
		$pk = $this->db->lastInsertID;
		if (!empty($attrs)) {
			$this->batchInsert(
				$this::TABLE_RESOURCE_TYPE_ATTRIBUTE,
				['resource_type_id', 'name', 'type', 'required', 'description'],
				array_map(function ($attr) use ($pk) {
					return [
						$pk,
						$attr['name'],
						@$attr['type'] ?? $this::ATTR_TYPE_FILE,
						@$attr['required'] ?? false,
						@$attr['description'],
					];
				}, $attrs)
			);
		}
	}

	public function safeDown() {
		$this->delete($this::TABLE_RESOURCE_TYPE, ['like', 'description', 'Тестовый тип (%', false]);
	}
}
