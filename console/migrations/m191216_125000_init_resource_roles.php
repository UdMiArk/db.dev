<?php

use console\components\Migration;

class m191216_125000_init_resource_roles extends Migration {
	const PREFIX = 'app.resource.';

	const ROLE_WATCHER = self::PREFIX . 'watcher';
	const ROLE_CREATOR = self::PREFIX . 'creator';
	const ROLE_MODERATOR = self::PREFIX . 'moderator';

	const PERM_ALL = self::PREFIX . '*';
	const PERM_VIEW = self::PREFIX . 'view';
	const PERM_CREATE = self::PREFIX . 'create';
	const PERM_UPDATE = self::PREFIX . 'update';
	const PERM_DELETE = self::PREFIX . 'delete';
	const PERM_APPROVE = self::PREFIX . 'approve';
	const PERM_ARCHIVE = self::PREFIX . 'archive';

	public function safeUp() {
		$auth = \Yii::$app->authManager;

		$rModerator = $auth->createRole($this::ROLE_MODERATOR);
		$rModerator->description = "Доступ к утверждению ресурсов";
		$rModerator->data = ['name' => 'Модератор ресурсов', 'group' => "Ресурсы"];
		$auth->add($rModerator);

		$rCreator = $auth->createRole($this::ROLE_CREATOR);
		$rCreator->description = "Доступ к добавлению и просмотру ресурсов";
		$rCreator->data = ['name' => 'Создатель ресурсов', 'group' => "Ресурсы"];
		$auth->add($rCreator);

		$rWatcher = $auth->createRole($this::ROLE_WATCHER);
		$rWatcher->description = "Доступ к просмотру ресурсов";
		$rWatcher->data = ['name' => 'Наблюдатель ресурсов', 'group' => "Ресурсы"];
		$auth->add($rWatcher);
		$auth->addChild($rModerator, $rWatcher);

		$pAll = $auth->createPermission($this::PERM_ALL);
		$pAll->description = "Полный доступ к действиям над ресурсами";
		$auth->add($pAll);

		$pView = $auth->createPermission($this::PERM_VIEW);
		$pView->description = "Просмотр ресурсов";
		$auth->add($pView);
		$auth->addChild($pAll, $pView);
		$auth->addChild($rWatcher, $pView);
		$auth->addChild($rCreator, $pView);

		$pCreate = $auth->createPermission($this::PERM_CREATE);
		$pCreate->description = "Создание ресурсов";
		$auth->add($pCreate);
		$auth->addChild($pAll, $pCreate);
		$auth->addChild($rCreator, $pCreate);

		$pUpdate = $auth->createPermission($this::PERM_UPDATE);
		$pUpdate->description = "Редактирование ресурсов";
		$auth->add($pUpdate);
		$auth->addChild($pAll, $pUpdate);

		$pDelete = $auth->createPermission($this::PERM_DELETE);
		$pDelete->description = "Удаление ресурсов";
		$auth->add($pDelete);
		$auth->addChild($pAll, $pDelete);

		$pApprove = $auth->createPermission($this::PERM_APPROVE);
		$pApprove->description = "Утверждение ресурсов";
		$auth->add($pApprove);
		$auth->addChild($pAll, $pApprove);
		$auth->addChild($rModerator, $pApprove);

		$pArchive = $auth->createPermission($this::PERM_ARCHIVE);
		$pArchive->description = "Архивация ресурсов";
		$auth->add($pArchive);
		$auth->addChild($pAll, $pArchive);

		$auth->invalidateCache();
	}

	public function safeDown() {
		$auth = \Yii::$app->authManager;

		$auth->remove($auth->getPermission($this::PERM_ARCHIVE));
		$auth->remove($auth->getPermission($this::PERM_APPROVE));
		$auth->remove($auth->getPermission($this::PERM_DELETE));
		$auth->remove($auth->getPermission($this::PERM_UPDATE));
		$auth->remove($auth->getPermission($this::PERM_CREATE));
		$auth->remove($auth->getPermission($this::PERM_VIEW));
		$auth->remove($auth->getPermission($this::PERM_ALL));

		$auth->remove($auth->getRole($this::ROLE_MODERATOR));
		$auth->remove($auth->getRole($this::ROLE_CREATOR));
		$auth->remove($auth->getRole($this::ROLE_WATCHER));

		$auth->invalidateCache();
	}
}
