<?php

use console\components\Migration;

class m200110_132000_separate_link_field_for_type extends Migration {
	const TABLE_RESOURCE_TYPE = '{{%resource-type}}';

	public function up() {
		$this->addColumn($this::TABLE_RESOURCE_TYPE, 'description_link', $this->string()->null());
		return parent::up();
	}

	public function safeUp() {
		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Новость на собственные сайты, сторонние ресурсы',
			'description_link' => 'https://erp.db.dev/wiki/новость_правила',
		], ['name' => 'Новость']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Баннер на собственные сайты, сторонние ресурсы, в соц сети',
			'description_link' => 'https://erp.db.dev/wiki/баннер_правила',
		], ['name' => 'Баннер']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Карта товара на собственные сайты',
			'description_link' => 'https://erp.db.dev/wiki/карта_товара',
		], ['name' => 'Карта товара на сайт']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Собственная рассылка, сторонняя рассылка',
			'description_link' => 'https://erp.db.dev/wiki/email',
		], ['name' => 'E-mail']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Листовка корпоративная, различных форматов',
			'description_link' => 'https://erp.db.dev/wiki/листовки',
		], ['name' => 'Листовка']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Брошюра корпоративная, различных форматов',
			'description_link' => 'https://erp.db.dev/wiki/брошюра',
		], ['name' => 'Брошюра']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Единый, общий ежегодный каталог',
			'description_link' => 'https://erp.db.dev/wiki/брошюра',
		], ['name' => 'Каталог']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Unpaking, короткие рекламные ролики',
			'description_link' => 'https://erp.db.dev/wiki/видео',
		], ['name' => 'Видео']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Видео курсы по настройке, программированию',
			'description_link' => null,
		], ['name' => 'Обучающее видео']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Запись вебинара',
			'description_link' => null,
		], ['name' => 'Вебинар видео']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Рендеры продукта с разных ракурсов',
			'description_link' => null,
		], ['name' => 'Изображение продукта']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Функциональная схема, обозначение при заказе, схема подключения и любые другие объекты, применяемые в других типах материалов',
			'description_link' => null,
		], ['name' => 'Изображение']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Gif-анимация для публикаций в соцсетях',
			'description_link' => 'https://erp.db.dev/wiki/smm',
		], ['name' => 'Gif-анимация']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Статья в журнале, сторонние ресурсы онлайн и оффлайн',
			'description_link' => null,
		], ['name' => 'Статья']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Слайды в общую презентацию, презентация для обучения сотрудников, дилеров, презентация для клиентов',
			'description_link' => null,
		], ['name' => 'Презентация']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Собственные аккаунты, платные посты, рекламные записи',
			'description_link' => 'https://erp.db.dev/wiki/smm',
		], ['name' => 'Пост в соцсеть']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => '1/4 полосы, 1/2 полосы, полоса, и др форматы в печатные издания',
			'description_link' => null,
		], ['name' => 'Рекламный модуль']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Анонс мероприятия на собственных сайтах',
			'description_link' => null,
		], ['name' => 'Страница мероприятия']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Форма заявки бета-теста, тест-драйва на собственные сайты',
			'description_link' => null,
		], ['name' => 'Заявки на тест']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Макет стенда для производства и файл с информацией о подключении',
			'description_link' => 'https://erp.db.dev/wiki/стенд',
		], ['name' => 'Стенд']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => null,
			'description_link' => null,
		], ['name' => 'Плакат']);

		$this->update($this::TABLE_RESOURCE_TYPE, [
			'description' => 'Реклама на поиске, КМС/РСЯ и тд.',
			'description_link' => null,
		], ['name' => 'Контекстная компания']);
	}

	public function down() {
		$this->dropColumn($this::TABLE_RESOURCE_TYPE, 'description_link');
	}
}
