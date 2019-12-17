<?php

use console\components\Migration;

class m191216_131500_set_default_roles extends Migration {
	const ROLE_DEFAULT = 'app.default';
	const ROLE_RESOURCE_CREATOR = 'app.resource.creator';
	const ROLE_RESOURCE_WATCHER = 'app.resource.watcher';

	public function safeUp() {
		$auth = Yii::$app->authManager;

		$rCreator = $auth->getRole($this::ROLE_RESOURCE_CREATOR);
		$rWatcher = $auth->getRole($this::ROLE_RESOURCE_WATCHER);
		$rCreator->data['hidden'] = true;
		$rWatcher->data['hidden'] = true;
		$auth->update($rCreator->name, $rCreator);
		$auth->update($rWatcher->name, $rWatcher);

		$auth->addChild($auth->getRole($this::ROLE_DEFAULT), $rCreator);

		$auth->invalidateCache();
	}

	public function safeDown() {
		$auth = Yii::$app->authManager;

		$rCreator = $auth->getRole($this::ROLE_RESOURCE_CREATOR);
		$rWatcher = $auth->getRole($this::ROLE_RESOURCE_WATCHER);

		$auth->removeChild($auth->getRole($this::ROLE_DEFAULT), $auth->getRole($this::ROLE_RESOURCE_CREATOR));

		unset($rCreator->data['hidden']);
		unset($rWatcher->data['hidden']);

		$auth->update($rCreator->name, $rCreator);
		$auth->update($rWatcher->name, $rWatcher);

		$auth->invalidateCache();
	}
}
