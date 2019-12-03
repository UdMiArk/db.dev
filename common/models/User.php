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
 * @property integer $status
 */
class User extends CommonRecord implements IdentityInterface {
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


	public static function findIdentity($id) {
		return static::findOne($id);
	}

	public static function findIdentityByAccessToken($token, $type = null) {
		throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
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
