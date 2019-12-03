<?php


namespace console\controllers;

use common\components\Adldap2Wrapper;
use common\components\AdldapHelper;
use console\components\ConsoleController;
use yii\console\Exception;

class ServiceController extends ConsoleController {
	public function actionEnsureUser($login, $domain = null) {
		$password = $this->prompt("Введите пароль:");

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
		if (!$provider->auth()->attempt($login, $password, true)) {
			throw new Exception('Неизвестная комбинация имени пользователя и пароля');
		}

		$user = AdldapHelper::ensureUserExists($login, $domain, $provider);

		$this->stdoutNl("ID обновленного пользователя: " . $user->primaryKey);

		return 0;
	}
}