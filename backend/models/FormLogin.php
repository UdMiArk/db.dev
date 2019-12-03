<?php


namespace backend\models;

use Adldap\Connections\Provider;
use common\components\Adldap2Wrapper;
use common\components\AdldapHelper;
use common\components\FormModel;

/**
 * Class FormLogin
 * @package backend\models
 *
 * @property-read Adldap2Wrapper $adManager
 */
class FormLogin extends FormModel {
	const REMEMBER_ME_DURATION = 2592000;

	public $login;
	public $domain;
	public $password;
	public $rememberMe;

	public function init() {
		parent::init();
		$this->rememberMe = true;
		$this->domain = $this->adManager->defaultProvider;
	}

	public function attributeLabels() {
		return [
			'domain' => "Домен",
			'login' => "Логин",
			'password' => "Пароль",
			'rememberMe' => "Запомнить сессию",
		];
	}

	/**
	 * @return Adldap2Wrapper
	 */
	public function getAdManager() {
		return \Yii::$app->ad;
	}

	public function getAvailableProviders() {
		$providers = $this->adManager->getProviders();
		$result = [];
		foreach ($providers as $key => $provider) {
			/* @var Provider $provider */
			$suffix = $provider->getConfiguration()->get('account_suffix');
			$result[$key] = $suffix ? $suffix : $key;
		}
		return $result;
	}

	public function rules() {
		return [
			[['login'], 'trim'],
			[['login', 'domain', 'password'], 'string'],
			[['rememberMe'], 'boolean'],
			[['domain'], 'in', 'range' => array_keys($this->adManager->getProviders()), 'message' => 'Указан неизвестный домен'],

			[['login', 'domain', 'password'], 'required'],
		];
	}

	public function execute() {
		if (!$this->validate()) {
			return false;
		}
		$adManager = $this->adManager;
		$provider = $adManager->getProvider($this->domain);

		if (!$provider->auth()->attempt($this->login, $this->password, true)) {
			$this->addError('password', 'Неизвестная комбинация имени пользователя и пароля');
			return false;
		}
		$user = AdldapHelper::ensureUserExists($this->login, $this->domain, $provider);
		if (!$user) {
			return false;
		}
		return \Yii::$app->user->login($user, $this->rememberMe ? $this::REMEMBER_ME_DURATION : 0);
	}
}