<?php


namespace backend\models;

use common\components\FormModel;
use common\components\ProductAccessibilityHelper;
use common\models\User;

/**
 * Class FormScLogin
 * @package backend\models
 */
class FormScLogin extends FormModel {
	public $email;
	public $password;

	public function attributeLabels() {
		return [
			'email' => "E-mail",
			'password' => "Пароль",
		];
	}

	public function rules() {
		return [
			[['email'], 'trim'],
			[['email', 'password'], 'string'],
			[['email', 'password'], 'required'],
		];
	}

	public function execute(User $user) {
		if (!$this->validate()) {
			return false;
		}
		if (!ProductAccessibilityHelper::updateUserKey($user, $this->email, $this->password, $errors)) {
			if (@$errors['not_found']) {
				$this->addError('password', "Введенная комбинация e-mail и пароля не найдена");
			}
			if (@$errors['problem']) {
				$this->addError('email', $errors['problem']);
			}
			return false;
		}
		return true;
	}
}