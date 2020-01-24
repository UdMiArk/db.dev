<?php


namespace common\components;


use common\models\Resource;
use common\models\User;
use yii\helpers\Json;

class ProductAccessibilityHelper {
	const TRUSTED_USER_KEY = 'trusted';
	const PRODUCT_CACHE_PREFIX = 'available_products_cache_';

	private static $_availableProducts = [];

	public static function getConfig() {
		return @\Yii::$app->params['resourcesServer'];
	}

	public static function getSecretKey() {
		return @static::getConfig()['skey'];
	}

	protected static function _encodeURIComponent($str) {
		$revert = ['%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')'];
		return strtr(rawurlencode($str), $revert);
	}

	protected static function _requestUserCheck($email, $password = null) {
		$config = static::getConfig();

		$address =
			$config['login']
			. '?key=' . static::_encodeURIComponent(static::getSecretKey())
			. '&email=' . static::_encodeURIComponent($email);
		if (!is_null($password)) {
			// FIXME: Really bad way to manage password, shouldn't be processed on DB at all
			$address .= '&password=' . static::_encodeURIComponent(\Yii::$app->security->encryptByPassword($password, static::getSecretKey()));
		}
		return Json::decode(@file_get_contents($address, false, stream_context_create([
			'http' => ['timeout' => $config['requests_timeout']],
		])));
	}

	protected static function _requestUserProducts($userKey) {
		$config = static::getConfig();
		$cacheTimeout = @$config['product_cache_timeout'];
		$result = false;
		if ($cacheTimeout) {
			$result = \Yii::$app->cache->get(static::PRODUCT_CACHE_PREFIX . $userKey);
		}
		if ($result === false) {
			$address =
				$config['action']
				. '?key=' . static::_encodeURIComponent(static::getSecretKey())
				. '&user_key=' . static::_encodeURIComponent($userKey);
			$result = @file_get_contents($address, false, stream_context_create([
				'http' => ['timeout' => $config['requests_timeout']],
			]));
			if ($result && $cacheTimeout) {
				\Yii::$app->cache->set(static::PRODUCT_CACHE_PREFIX . $userKey, $result, $cacheTimeout);
			}
		}

		return $result ? Json::decode($result) : null;
	}

	public static function updateUserKey(User $user, $email, $password, &$errors = []) {
		$checkData = static::_requestUserCheck($email, $password ?? ' ');
		if (!($checkData && $checkData['found'])) {
			$errors['not_found'] = "Не удалось соединить с пользователем SC";
			return false;
		}
		if (@$checkData['problem']) {
			$errors['problem'] = $checkData['problem'];
			return false;
		}
		$user->sc_key = $checkData['user_key'];
		if (!$user->isAttributeChanged('sc_key')) {
			return true;
		}
		return $user->update();
	}

	public static function getAvailableProducts(User $user, &$errors = []) {
		$isUserTrusted = \Yii::$app->authManager->checkAccess(
			$user->primaryKey,
			Resource::RBAC_AUTO_APPROVE
		);

		if (!$isUserTrusted && !$user->sc_key) {
			$checkData = static::_requestUserCheck($user->email);
			if (!($checkData && $checkData['found'])) {
				$errors['not_found'] = "Не удалось определить пользователя SC";
				return false;
			}
			if (@$checkData['problem']) {
				$errors['problem'] = $checkData['problem'];
				return false;
			}
			$user->sc_key = $checkData['user_key'];
			$user->update();
		}

		$userKey = $isUserTrusted ? static::TRUSTED_USER_KEY : $user->sc_key;
		if (array_key_exists($userKey, static::$_availableProducts)) {
			return static::$_availableProducts[$userKey];
		}

		$productsResponse = static::_requestUserProducts($userKey);
		if (!($productsResponse && $productsResponse['user_found'])) {
			$errors['not_found'] = "Связанный пользователь SC не найден";
			$user->sc_key = null;
			$user->update();
			return false;
		}

		if (@$productsResponse['problem']) {
			$errors['problem'] = $productsResponse['problem'];
			return false;
		}

		static::$_availableProducts[$userKey] = $productsResponse['products'];

		return $productsResponse['products'];
	}
}