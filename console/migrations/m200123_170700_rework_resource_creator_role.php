<?php

use console\components\Migration;

class m200123_170700_rework_resource_creator_role extends Migration {
	const PREFIX = 'app.resource.';

	const ROLE_CREATOR = self::PREFIX . 'creator';
	const ROLE_MODERATOR = self::PREFIX . 'moderator';

	const PERM_CONTROLLED_PRODUCT = self::PREFIX . 'is_controlled_product';

	const PERM_DELETE = self::PREFIX . 'delete';
	const PERM_ARCHIVE = self::PREFIX . 'archive';

	public function safeUp() {
		$auth = Yii::$app->authManager;

		$rCreator = $auth->getRole($this::ROLE_CREATOR);
		$rModerator = $auth->getRole($this::ROLE_MODERATOR);
		$pDelete = $auth->getPermission($this::PERM_DELETE);
		$pArchive = $auth->getPermission($this::PERM_ARCHIVE);

		$ruleCanControlProduct = new \common\rbac\RBACRuleCanControlProduct();
		$auth->add($ruleCanControlProduct);

		$pControlledProduct = $auth->createPermission($this::PERM_CONTROLLED_PRODUCT);
		$pControlledProduct->description = "Проверка наличия контроля над объектом продвижения";
		$pControlledProduct->ruleName = $ruleCanControlProduct->name;
		$auth->add($pControlledProduct);
		$auth->addChild($rCreator, $pControlledProduct);
		$auth->addChild($pControlledProduct, $pDelete);
		$auth->addChild($pControlledProduct, $pArchive);
		$auth->addChild($rModerator, $pArchive);

		$auth->invalidateCache();
	}

	public function safeDown() {
		$auth = Yii::$app->authManager;

		$auth->removeChild($auth->getRole($this::ROLE_MODERATOR), $auth->getPermission($this::PERM_ARCHIVE));

		$auth->remove($auth->getPermission($this::PERM_CONTROLLED_PRODUCT));
		$auth->remove($auth->getRule((new \common\rbac\RBACRuleCanControlProduct())->name));

		$auth->invalidateCache();
	}
}
