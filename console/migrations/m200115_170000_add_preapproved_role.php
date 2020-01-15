<?php

use console\components\Migration;

class m200115_170000_add_preapproved_role extends Migration {
	const PREFIX = 'app.resource.';

	const ROLE_TRUSTED = self::PREFIX . 'trusted_creator';
	const ROLE_CREATOR = self::PREFIX . 'creator';

	const PERM_ALL = self::PREFIX . '*';
	const PERM_APPROVE = self::PREFIX . 'approve';
	const PERM_AUTO_APPROVE = self::PREFIX . 'approve_own';

	public function safeUp() {
		$auth = Yii::$app->authManager;

		$rTrusted = $auth->createRole($this::ROLE_TRUSTED);
		$rTrusted->description = "Автоматическое утверждение созданных ресурсов";
		$rTrusted->data = ['name' => 'Доверенный создатель', 'group' => "Ресурсы"];
		$auth->add($rTrusted);

		$rCreator = $auth->getRole($this::ROLE_CREATOR);
		$auth->addChild($rTrusted, $rCreator);

		$pAll = $auth->getPermission($this::PERM_ALL);
		$pApprove = $auth->getPermission($this::PERM_APPROVE);

		$pAutoApproval = $auth->createPermission($this::PERM_AUTO_APPROVE);
		$pAutoApproval->description = "Автоматическое утверждение созданных ресурсов";
		$auth->add($pAutoApproval);
		$auth->addChild($pAll, $pAutoApproval);
		$auth->addChild($pApprove, $pAutoApproval);
		$auth->addChild($rTrusted, $pAutoApproval);

		$auth->invalidateCache();
	}

	public function safeDown() {
		$auth = Yii::$app->authManager;

		$auth->remove($auth->getPermission($this::PERM_AUTO_APPROVE));

		$auth->remove($auth->getRole($this::ROLE_TRUSTED));

		$auth->invalidateCache();
	}
}
