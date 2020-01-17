<?php

use console\components\Migration;

class m200117_153000_rename_preapproved_role extends Migration {
	const PREFIX = 'app.resource.';

	const ROLE_TRUSTED = self::PREFIX . 'trusted_creator';

	public function safeUp() {
		$auth = Yii::$app->authManager;

		$rTrusted = $auth->getRole($this::ROLE_TRUSTED);
		$rTrusted->data = ['name' => 'Доверенный пользователь', 'group' => "Ресурсы"];
		$auth->update($rTrusted->name, $rTrusted);

		$auth->invalidateCache();
	}

	public function safeDown() {
		$auth = Yii::$app->authManager;

		$rTrusted = $auth->getRole($this::ROLE_TRUSTED);
		$rTrusted->data = ['name' => 'Доверенный создатель', 'group' => "Ресурсы"];
		$auth->update($rTrusted->name, $rTrusted);

		$auth->invalidateCache();
	}
}
