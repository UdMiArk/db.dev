<?php


namespace common\components;


use Adldap\Connections\Provider;
use common\models\User;
use yii\base\Exception;

class AdldapHelper {

	/**
	 * @param string $login
	 * @param string $domain
	 * @param Provider $provider
	 *
	 * @return User
	 * @throws Exception
	 */
	public static function ensureUserExists($login, $domain, $provider) {
		$user = User::findByLogin($login, $domain);
		if (!$user) {
			$user = new User();
			$user->login = $login;
			$user->domain = $domain;
			$user->generateAuthKey();
		}

		static::updateLdapData($user, $provider);
		return $user;
	}

	/**
	 * @param User $user
	 * @param Provider $provider
	 * @throws Exception
	 */
	public static function updateLdapData(User $user, Provider $provider) {
		// Fill in test user for debugging without need for working LDAP Server
		if (defined('YII_DEBUG') && YII_DEBUG && $user->login === 'test.user') {
			$user->email = 'user@db.dev';
			$user->name = "Тестовый пользователь";
		} else {
			$data = static::getUserLdapData($user, $provider);
			if (!$data) {
				throw new Exception("Не удалось найти LDAP пользователя: " . $user->login . $user->domain);
			}

			/* @var \Adldap\Models\User $data */
			$user->email = $data->getEmail() ?? $data->getUserPrincipalName();
			$user->name = $data->getDisplayName();
		}

		if ($user->isNewRecord || !empty($user->getDirtyAttributes())) {
			if (!$user->save()) {
				throw new Exception("Не удалось обновить пользователя: " . $user->getFirstError());
			}
		}
	}

	public static function searchUserLdapDataDataByLogin($login, Provider $provider) {
		if (!$provider->getConnection()->isBound()) {
			$provider->auth()->bindAsAdministrator();
		}
		$data = $provider->search()->where('sAMAccountname', '=', $login)->get();
		if (count($data)) {
			return $data[0];
		} else {
			return null;
		}
	}

	/**
	 * @param User $user
	 * @param Provider $provider
	 *
	 * @return \Adldap\Models\User
	 */
	public static function getUserLdapData(User $user, Provider $provider) {
		return static::searchUserLdapDataDataByLogin($user->login, $provider);
	}
}