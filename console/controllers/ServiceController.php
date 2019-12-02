<?php


namespace console\controllers;

use common\models\User;
use console\components\ConsoleController;
use yii\console\Exception;

class ServiceController extends ConsoleController {
	public function actionAddUser($login, $name, $email) {
		$user = new User();
		$user->login = $login;
		$user->email = $email;
		$user->name = $name;
		$user->generateAuthKey();
		if (!$user->save()) {
			throw new Exception("Failed to save user: " . $user->getFirstError());
		}
		print "Done\n";
		return 0;
	}
}