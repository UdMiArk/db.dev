<?php


namespace common\components;


use common\models\User;

class ProductAccessibilityHelper {
	public static function getAvailableProducts(User $user) {
		if (static::checkIfUserCanGetAvailableProducts($user)) {
			return [
				[
					'id' => 123,
					'market' => 1,
					'name' => 'ПРМ100',
				],
			];
		}
		return false;
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public static function checkIfUserCanGetAvailableProducts(User $user) {
		return false;
	}
}