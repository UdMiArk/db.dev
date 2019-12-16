<?php

use console\components\Migration;

class m191216_131500_set_default_roles extends Migration {
	const ROLE_DEFAULT = 'app.default';
	const ROLE_RESOURCE_CREATOR = 'app.resource.creator';

	public function safeUp() {
		$auth = \Yii::$app->authManager;

		$auth->addChild($auth->getRole($this::ROLE_DEFAULT), $auth->getRole($this::ROLE_RESOURCE_CREATOR));

		$auth->invalidateCache();
	}

	public function safeDown() {
		$auth = \Yii::$app->authManager;

		$auth->removeChild($auth->getRole($this::ROLE_DEFAULT), $auth->getRole($this::ROLE_RESOURCE_CREATOR));

		$auth->invalidateCache();
	}
}
