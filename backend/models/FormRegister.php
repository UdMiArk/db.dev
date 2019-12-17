<?php


namespace backend\models;

use Adldap\Connections\Provider;
use common\components\Adldap2Wrapper;
use common\components\AdldapHelper;
use common\components\FormModel;
use common\models\User;

/**
 * Class FormLogin
 * @package backend\models
 *
 * @property-read Adldap2Wrapper $adManager
 */
class FormRegister extends FormModel {
	public $login;
	public $domain;

	public function init() {
		parent::init();
		$this->domain = $this->adManager->defaultProvider;
	}

	public function attributeLabels() {
		return [
			'domain' => "Домен",
			'login' => "Логин",
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
			[['login', 'domain'], 'string'],
			[['domain'], 'in', 'range' => array_keys($this->adManager->getProviders()), 'message' => 'Указан неизвестный домен'],

			[['login', 'domain'], 'required'],
		];
	}

	public function execute() {
		if (!$this->validate()) {
			return false;
		}

		$user = User::findByLogin($this->login, $this->domain);
		if ($user) {
			$this->addError('login', 'Пользователь уже зарегестрирован');
			return false;
		}
		$adManager = $this->adManager;
		$provider = $adManager->getProvider($this->domain);

		$userData = AdldapHelper::searchUserLdapDataDataByLogin($this->login, $provider);
		if (!$userData) {
			$this->addError('login', 'Указанный пользователь не найден в Active Directory');
			return false;
		}

		$user = AdldapHelper::ensureUserExists($this->login, $this->domain, $provider);
		if (!$user) {
			return false;
		}
		return $user;
	}
}