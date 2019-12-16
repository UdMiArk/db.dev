<?php

use console\components\Migration;

class m191216_124000_init_app_roles extends Migration {
	const ROLE_DEFAULT = 'app.default';
	const ROLE_SUPERUSER = 'app.superuser';

	public function safeUp() {
		$auth = \Yii::$app->authManager;

		$rSuperuser = $auth->createRole($this::ROLE_SUPERUSER);
		$rSuperuser->description = "!Полный доступ к приложению!";
		$rSuperuser->data = ['name' => 'Администратор'];
		$auth->add($rSuperuser);

		$rDefault = $auth->createRole($this::ROLE_DEFAULT);
		$rDefault->description = "Роль имеющаяся у всех пользователей";
		$rDefault->data = ['name' => 'Пользователь'];
		$auth->add($rDefault);

		$auth->invalidateCache();
	}

	public function safeDown() {
		$auth = \Yii::$app->authManager;

		$auth->remove($auth->getRole($this::ROLE_DEFAULT));
		$auth->remove($auth->getRole($this::ROLE_SUPERUSER));

		$auth->invalidateCache();
	}
}
