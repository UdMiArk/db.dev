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
	public $remember;

	public function init() {
		parent::init();
		$this->remember = true;
		$this->domain = $this->adManager->defaultProvider;
	}

	public function attributeLabels() {
		return [
			'domain' => "Домен",
			'login' => "Логин",
			'password' => "Пароль",
			'remember' => "Запомнить сессию",
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
		$firstProvider = $this->domain;
		$result [] = [
			'value' => $firstProvider,
			'label' => $providers[$firstProvider]->getConfiguration()->get('account_suffix') ?? $firstProvider,
		];
		foreach ($providers as $key => $provider) {
			/* @var Provider $provider */
			if ($key !== $firstProvider) {
				$result [] = [
					'value' => $key,
					'label' => $provider->getConfiguration()->get('account_suffix') ?? $key,
				];
			}
		}
		return $result;
	}

	public function rules() {
		return [
			[['login'], 'trim'],
			[['login', 'domain', 'password'], 'string'],
			[['remember'], 'boolean'],
			[['domain'], 'in', 'range' => array_keys($this->adManager->getProviders()), 'message' => 'Указан неизвестный домен'],

			[['login', 'domain', 'password'], 'required'],

			[['login'], 'ruleLoginWithoutDomainSuffix'],
		];
	}

	public function ruleLoginWithoutDomainSuffix($attr) {
		if ($this->{$attr} && $this->domain) {
			$login = $this->{$attr};
			$suffix = $this->adManager->getProvider($this->domain)->getConfiguration()->get('account_suffix');
			if ($suffix) {
				$offset = mb_strlen($login) - mb_strlen($suffix);
				if ($offset > 0 && mb_substr($login, $offset) === $suffix) {
					$this->{$attr} = mb_substr($login, 0, $offset);
				}
			}
		}
	}

	public function execute() {
		if (!$this->validate()) {
			return false;
		}
		$adManager = $this->adManager;
		$provider = $adManager->getProvider($this->domain);

		//if ($this->login !== 'test.user') {
		if (!$provider->auth()->attempt($this->login, $this->password/*, true*/)) {
			$this->addError('password', 'Неизвестная комбинация имени пользователя и пароля');
			return false;
		}
		//}
		$user = AdldapHelper::ensureUserExists($this->login, $this->domain, $provider);
		if (!$user) {
			return false;
		}
		return \Yii::$app->user->login($user, $this->remember ? $this::REMEMBER_ME_DURATION : 0);
	}
}