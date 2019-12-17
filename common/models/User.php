<?php

namespace common\models;

use common\components\CommonRecord;
use common\components\DateTimeStampBehavior;
use Yii;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $login
 * @property string $name
 * @property string $email
 * @property string $domain
 * @property string $auth_key
 * @property string $sc_key - Key to identify user on erp.db.dev
 * @property integer $status
 */
class User extends CommonRecord implements IdentityInterface {
	const RBAC_MANAGE = 'app.user.manage';

	public function attributeLabels() {
		return [
			'created_at' => "Дата регистрации",
			'login' => "Логин",
			'name' => "Имя",
			'email' => "E-mail",
			'domain' => "Домен",
			'status' => "Статус",
		];
	}

	public function behaviors() {
		return [
			DateTimeStampBehavior::class,
		];
	}

	public function getFrontendInfo($extendedInfo = false) {
		$result = array_merge(parent::getFrontendInfo(), [
			'name' => $this->name,
			'email' => $this->email,
		]);
		if ($extendedInfo) {
			$result = array_merge($result, [
				'login' => $this->login,
				'domain' => $this->domain,
				'status' => $this->status,
				'created_at' => $this->created_at,
			]);
		}
		return $result;
	}

	/**
	 * Finds user by login
	 *
	 * @param string $login
	 * @param string $domain
	 * @return static|null
	 */
	public static function findByLogin($login, $domain) {
		return static::findOne(['domain' => $domain, 'login' => $login]);
	}

	// IdentityInterface methods
	public static function findIdentity($id) {
		return static::findOne($id);
	}

	public static function findIdentityByAccessToken($token, $type = null) {
		throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
	}

	public function getId() {
		return $this->getPrimaryKey();
	}

	public function validateAuthKey($authKey) {
		return $this->getAuthKey() === $authKey;
	}

	public function getAuthKey() {
		return $this->auth_key;
	}

	/**
	 * Generates "remember me" authentication key
	 */
	public function generateAuthKey() {
		$this->auth_key = Yii::$app->security->generateRandomString();
	}
}
