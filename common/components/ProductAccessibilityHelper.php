<?php


namespace common\components;


use common\models\User;
use yii\helpers\Json;

class ProductAccessibilityHelper {
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
		$address =
			static::getConfig()['login']
			. '?key=' . static::_encodeURIComponent(static::getSecretKey())
			. '&email=' . static::_encodeURIComponent($email);
		if (!is_null($password)) {
			// FIXME: Really bad way to manage password, shouldn't be processed on DB at all
			$address .= '&password=' . static::_encodeURIComponent(\Yii::$app->security->encryptByPassword($password, static::getSecretKey()));
		}
		$ctx = stream_context_create(['http' =>
			[
				'timeout' => 15,
			],
		]);
		return Json::decode(@file_get_contents($address, false, $ctx));
	}

	protected static function _requestUserProducts($userKey) {
		$ctx = stream_context_create(['http' =>
			[
				'timeout' => 15,
			],
		]);
		$address =
			static::getConfig()['action']
			. '?key=' . static::_encodeURIComponent(static::getSecretKey())
			. '&user_key=' . static::_encodeURIComponent($userKey);
		return Json::decode(@file_get_contents($address, false, $ctx));
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
		if (!$user->sc_key) {
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

		$productsResponse = static::_requestUserProducts($user->sc_key);
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

		return $productsResponse['products'];
	}
}