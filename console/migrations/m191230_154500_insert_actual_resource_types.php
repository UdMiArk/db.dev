<?php

use console\components\Migration;
use yii\helpers\Json;

class m191230_154500_insert_actual_resource_types extends Migration {
	const TABLE_RESOURCE = '{{%resource}}';
	const TABLE_PRODUCT = '{{%product}}';
	const TABLE_RESOURCE_TYPE = '{{%resource-type}}';
	const TABLE_RESOURCE_TYPE_ATTRIBUTE = '{{%resource-type-attribute}}';

	const ATTR_TYPE_FILE = 10;
	const ATTR_TYPE_FILES = 20;

	public function up() {
		$this->db->createCommand("SET FOREIGN_KEY_CHECKS=0;")->execute();
		$this->truncateTable($this::TABLE_RESOURCE);
		$this->truncateTable($this::TABLE_PRODUCT);
		$this->truncateTable($this::TABLE_RESOURCE_TYPE_ATTRIBUTE);
		$this->truncateTable($this::TABLE_RESOURCE_TYPE);
		$this->db->createCommand("SET FOREIGN_KEY_CHECKS=1;")->execute();
		return parent::up();
	}

	public function safeUp() {
		$this->insertType(
			"Новость",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Текст новости",
					'required' => true,
					'options' => ['extensions' => '.doc,.docx'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Основное изображение",
					'required' => true,
					'options' => ['extensions' => '.png,.jpg'],
				],
				[
					'type' => $this::ATTR_TYPE_FILES,
					'name' => "Дополнительные файлы",
					'required' => false,
					'options' => ['extensions' => '.png,.jpg'],
				],
			],
			"Новость на собственные сайты, сторонние ресурсы.\n"
			. "Страница правил: https://erp.db.dev/wiki/новость_правила"
		);

		$this->insertType(
			"Баннер",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Основной файл",
					'required' => true,
					'options' => ['extensions' => '.png,.zip'],
					'description' => "может быть выложен архив с набором баннеров разного формата одной тематики",
				],
			],
			"Баннер на собственные сайты, сторонние ресурсы, в соц сети.\n"
			. "Страница правил: https://erp.db.dev/wiki/баннер_правила"
		);

		$this->insertType(
			"Карта товара на сайт",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Текст",
					'required' => true,
					'options' => ['extensions' => '.doc,.docx'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Основное изображение",
					'required' => true,
					'options' => ['extensions' => '.png'],
				],
				[
					'type' => $this::ATTR_TYPE_FILES,
					'name' => "Дополнительные файлы",
					'required' => true,
					'options' => ['extensions' => '.png,.svg,.pdf,.zip'],
				],
			],
			"Карта товара на собственные сайты.\n"
			. "Страница правил: https://erp.db.dev/wiki/карта_товара"
		);

		$this->insertType(
			"E-mail",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Текст",
					'required' => true,
					'options' => ['extensions' => '.txt,.html'],
				],
				[
					'type' => $this::ATTR_TYPE_FILES,
					'name' => "Дополнительные файлы",
					'required' => true,
					'options' => ['extensions' => '.png,.zip'],
				],
			],
			"Собственная рассылка, сторонняя рассылка.\n"
			. "Страница правил: https://erp.db.dev/wiki/email"
		);

		$this->insertType(
			"Листовка",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Прессформат",
					'required' => true,
					'options' => ['extensions' => '.pdf'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Preview",
					'required' => true,
					'options' => ['extensions' => '.jpg'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Редактируемый формат",
					'required' => true,
					'options' => ['extensions' => '.zip'],
				],
			],
			"Листовка корпоративная, различных форматов.\n"
			. "Страница правил: https://erp.db.dev/wiki/листовки"
		);

		$this->insertType(
			"Брошюра",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Прессформат",
					'required' => true,
					'options' => ['extensions' => '.pdf'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Preview",
					'required' => true,
					'options' => ['extensions' => '.jpg'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Редактируемый формат",
					'required' => true,
					'options' => ['extensions' => '.zip'],
				],
			],
			"Брошюра корпоративная, различных форматов.\n"
			. "Страница правил: https://erp.db.dev/wiki/брошюра"
		);

		$this->insertType(
			"Каталог",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Прессформат",
					'required' => true,
					'options' => ['extensions' => '.pdf'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Preview",
					'required' => true,
					'options' => ['extensions' => '.jpg'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Редактируемый формат",
					'required' => true,
					'options' => ['extensions' => '.zip'],
				],
			],
			"Единый, общий ежегодный каталог.\n"
			. "Страница правил: https://erp.db.dev/wiki/брошюра"
		);

		$this->insertType(
			"Видео",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Файл",
					'required' => true,
					'options' => ['extensions' => '.mp4,.mov,.zip'],
					'description' => "может быть выложен архив с набором роликов одного курса",
				],
			],
			"Unpaking, короткие рекламные ролики.\n"
			. "Страница правил: https://erp.db.dev/wiki/видео"
		);

		$this->insertType(
			"Обучающее видео",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Файл",
					'required' => true,
					'options' => ['extensions' => '.mp4,.mov,.zip'],
					'description' => "может быть выложен архив с набором роликов одного курса",
				],
			],
			"Видео курсы по настройке, программированию."
		);

		$this->insertType(
			"Вебинар видео",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Файл",
					'required' => true,
					'options' => ['extensions' => '.mp4,.zip'],
					'description' => "может быть выложен архив с набором роликов одного курса",
				],
			],
			"Запись вебинара."
		);

		$this->insertType(
			"Изображение продукта",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Основное изображение",
					'required' => true,
					'options' => ['extensions' => '.png'],
				],
				[
					'type' => $this::ATTR_TYPE_FILES,
					'name' => "Дополнительные файлы",
					'required' => false,
					'options' => ['extensions' => '.png,.pdf'],
				],
			],
			"Рендеры продукта с разных ракурсов"
		);

		$this->insertType(
			"Изображение",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Файл",
					'required' => true,
					'options' => ['extensions' => '.png,.jpg,.svg'],
				],
			],
			"Функциональная схема, обозначение при заказе, схема подключения и любые другие объекты, применяемые в других типах материалов."
		);

		$this->insertType(
			"Gif-анимация",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Файл",
					'required' => true,
					'options' => ['extensions' => '.gif'],
				],
			],
			"Gif-анимация для публикаций в соцсетях.\n"
			. "Страница правил: https://erp.db.dev/wiki/smm"
		);

		$this->insertType(
			"Статья",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Текст",
					'required' => true,
					'options' => ['extensions' => '.doc,.docx'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Основное изображение",
					'required' => true,
					'options' => ['extensions' => '.png,.jpg'],
				],
				[
					'type' => $this::ATTR_TYPE_FILES,
					'name' => "Дополнительные файлы",
					'required' => false,
					'options' => ['extensions' => '.png,.jpg'],
				],
			],
			"Статья в журнале, сторонние ресурсы онлайн и оффлайн."
		);

		$this->insertType(
			"Презентация",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Файл",
					'required' => true,
					'options' => ['extensions' => '.ppt,.pptx'],
				],
			],
			"Слайды в общую презентацию, презентация для обучения сотрудников, дилеров, презентация для клиентов."
		);

		$this->insertType(
			"Пост в соцсеть",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Текст поста",
					'required' => true,
					'options' => ['extensions' => '.doc,.docx'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Основной контент",
					'required' => true,
					'options' => ['extensions' => '.png,.mp4,.mov'],
				],
			],
			"Собственные аккаунты, платные посты, рекламные записи.\n"
			. "Страница правил: https://erp.db.dev/wiki/smm"
		);

		$this->insertType(
			"Рекламный модуль",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Прессформат",
					'required' => true,
					'options' => ['extensions' => '.pdf,.tiff'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Preview",
					'required' => true,
					'options' => ['extensions' => '.jpg'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Редактируемый формат",
					'required' => true,
					'options' => ['extensions' => '.zip'],
				],
			],
			"1/4 полосы, 1/2 полосы, полоса, и др форматы в печатные издания."
		);

		$this->insertType(
			"Страница мероприятия",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Текст",
					'required' => true,
					'options' => ['extensions' => '.doc,.docx'],
				],
				[
					'type' => $this::ATTR_TYPE_FILES,
					'name' => "Дополнительные файлы",
					'required' => false,
					'options' => ['extensions' => '.png'],
				],
			],
			"Анонс мероприятия на собственных сайтах."
		);

		$this->insertType(
			"Заявки на тест",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Текст",
					'required' => true,
					'options' => ['extensions' => '.xls'],
				],
				[
					'type' => $this::ATTR_TYPE_FILES,
					'name' => "Дополнительные файлы",
					'required' => false,
					'options' => ['extensions' => '.png,.zip'],
				],
			],
			"Форма заявки бета-теста, тест-драйва на собственные сайты."
		);

		$this->insertType(
			"Стенд",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Инструкция по подключению",
					'required' => true,
					'options' => ['extensions' => '.doc,.docx'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Прессформат",
					'required' => true,
					'options' => ['extensions' => '.eps'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Preview",
					'required' => true,
					'options' => ['extensions' => '.jpg'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Редактируемый формат",
					'required' => true,
					'options' => ['extensions' => '.zip'],
				],
			],
			"Макет стенда для производства и файл с информацией о подключении.\n"
			. "Страница правил: https://erp.db.dev/wiki/стенд"
		);

		$this->insertType(
			"Плакат",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Прессформат",
					'required' => true,
					'options' => ['extensions' => '.pdf,.tiff'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Preview",
					'required' => true,
					'options' => ['extensions' => '.jpg'],
				],
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Редактируемый формат",
					'required' => true,
					'options' => ['extensions' => '.zip'],
				],
			]
		);

		$this->insertType(
			"Контекстная компания",
			[
				[
					'type' => $this::ATTR_TYPE_FILE,
					'name' => "Основной файл",
					'required' => true,
					'options' => ['extensions' => '.xls'],
				],
				[
					'type' => $this::ATTR_TYPE_FILES,
					'name' => "Дополнительные файлы",
					'required' => false,
					'options' => ['extensions' => '.png,.zip'],
					'description' => "может быть выложен архив с набором баннеров разного формата одной тематики",
				],
			],
			"Реклама на поиске, КМС/РСЯ и тд."
		);
	}

	protected function insertType($name, $attrs, $description = null) {
		$this->insert($this::TABLE_RESOURCE_TYPE, ['name' => $name, 'description' => $description]);
		$pk = $this->db->lastInsertID;
		if (!empty($attrs)) {
			$this->batchInsert(
				$this::TABLE_RESOURCE_TYPE_ATTRIBUTE,
				['resource_type_id', 'name', 'type', 'required', 'description', 'options_json'],
				array_map(function ($attr) use ($pk) {
					return [
						$pk,
						$attr['name'],
						@$attr['type'] ?? $this::ATTR_TYPE_FILE,
						@$attr['required'] ?? false,
						@$attr['description'],
						@$attr['options'] ? Json::encode($attr['options']) : null,
					];
				}, $attrs)
			);
		}
	}

	public function down() {
		$this->db->createCommand("SET FOREIGN_KEY_CHECKS=0;")->execute();
		$this->truncateTable($this::TABLE_RESOURCE);
		$this->truncateTable($this::TABLE_PRODUCT);
		$this->truncateTable($this::TABLE_RESOURCE_TYPE_ATTRIBUTE);
		$this->truncateTable($this::TABLE_RESOURCE_TYPE);
		$this->db->createCommand("SET FOREIGN_KEY_CHECKS=1;")->execute();
		return parent::down();
	}
}
