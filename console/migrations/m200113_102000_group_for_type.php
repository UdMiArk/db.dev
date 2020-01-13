<?php

use console\components\Migration;

class m200113_102000_group_for_type extends Migration {
	const TABLE_RESOURCE_TYPE = '{{%resource-type}}';

	public function up() {
		$this->addColumn($this::TABLE_RESOURCE_TYPE, 'group_name', $this->string()->null());
		return parent::up();
	}

	public function safeUp() {
		$this->update($this::TABLE_RESOURCE_TYPE, [
			'group_name' => 'PR и SMM',
			'description_link' => "https://erp.db.dev/wiki/рекламный_модуль",
		], ['name' => 'Рекламный модуль']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'group_name' => 'Изображения и видео',
		], ['name' => ['Изображение продукта', 'Изображение', 'Видео', 'Вебинар видео', 'Обучающее видео']]);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'group_name' => 'PR и SMM',
		], ['name' => ['Новость', 'Статья', 'Пост в соцсеть', 'Gif-анимация']]);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'group_name' => 'Интернет продвижение',
		], ['name' => ['Карта товара на сайт', 'Баннер', 'E-mail', 'Заявки на тест', 'Контекстная компания']]);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'group_name' => 'Событийный маркетинг и POSM',
		], ['name' => ['Презентация', 'Стенд', 'Плакат', 'Страница мероприятия']]);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'group_name' => 'Полиграфия',
		], ['name' => ['Листовка', 'Брошюра', 'Каталог']]);
	}

	public function down() {
		$this->dropColumn($this::TABLE_RESOURCE_TYPE, 'group_name');
	}
}
