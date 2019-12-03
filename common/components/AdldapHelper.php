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
		$data = static::getUserLdapData($user, $provider);
		if (!$data) {
			throw new Exception("Не удалось найти LDAP пользователя: " . $user->login . $user->domain);
		}

		/* @var \Adldap\Models\User $data */
		$user->email = $data->getEmail();
		$user->name = $data->getDisplayName();

		if ($user->isNewRecord || !empty($user->getDirtyAttributes())) {
			if (!$user->save()) {
				throw new Exception("Не удалось обновить пользователя: " . $user->getFirstError());
			}
		}
	}

	/**
	 * @param User $user
	 * @param Provider $provider
	 *
	 * @return \Adldap\Models\User
	 */
	public static function getUserLdapData(User $user, Provider $provider) {
		$data = $provider->search()->where('sAMAccountname', '=', $user->login)->get();
		if (count($data)) {
			return $data[0];
		} else {
			return null;
		}
	}
}