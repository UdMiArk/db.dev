<?php


namespace console\controllers;

use common\components\Adldap2Wrapper;
use common\components\AdldapHelper;
use common\data\RBACData;
use console\components\ConsoleController;
use yii\console\Exception;

class ServiceController extends ConsoleController {
	protected function _ensureUserExists($login, $domain = null) {
		//$password = $this->prompt("Введите пароль:");

		/* @var Adldap2Wrapper $adManager */
		$adManager = \Yii::$app->ad;
		if (empty($domain)) {
			$domain = $adManager->defaultProvider;
		}

		$providers = $adManager->getProviders();
		if (!array_key_exists($domain, $providers)) {
			throw new Exception(
				"Не удалось определить домен " . $domain
				. ". Доступные: " . implode(', ', array_keys($providers))
			);
		}
		$provider = $providers[$domain];
		/*if (!$provider->auth()->attempt($login, $password, true)) {
			throw new Exception('Неизвестная комбинация имени пользователя и пароля');
		}*/

		return AdldapHelper::ensureUserExists($login, $domain, $provider);
	}

	public function actionEnsureUser($login, $domain = null) {
		$user = $this->_ensureUserExists($login, $domain);

		$this->stdoutNl("ID обновленного пользователя: " . $user->primaryKey);

		return 0;
	}

	public function actionAssignAdmin($login, $domain = null) {
		// Check that script execution is initiated by root/sudoer
		/* Commented out since it kinda meaningless:
		 * if bad guy have access to cli, he can just get DB auth details from config
		if (posix_getuid() !== 0) {
			throw new Exception("Назначить админа можно только выполняя команду из под root-пользователя");
		}
		*/

		$user = $this->_ensureUserExists($login, $domain);
		$authManager = \Yii::$app->authManager;
		$role = $authManager->getRole(RBACData::ROLE_SUPERUSER);
		if (!$role) {
			throw new Exception("Роль 'Администратор' не инициилизирована");
		}
		if (!$authManager->getAssignment($role->name, $user->primaryKey)) {
			$authManager->assign($role, $user->primaryKey);
		}
		$this->stdoutNl("Выполнено");
		return 0;
	}
}