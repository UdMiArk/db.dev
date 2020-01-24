<?php


namespace common\rbac;


use common\components\ProductAccessibilityHelper;
use common\models\Product;
use common\models\Resource;
use common\models\User;
use yii\rbac\Rule;

class RBACRuleCanControlProduct extends Rule {
	public $name = 'canControlProduct';

	/**
	 * @param string|int $user the user ID.
	 * @param \yii\rbac\Item $item the role or permission that this rule is associated with
	 * @param array $params parameters passed to ManagerInterface::checkAccess().
	 * @return bool a value indicating whether the rule permits the role or permission it is associated with.
	 */
	public function execute($user, $item, $params) {
		if (@$params['productId']) {
			return $this->_checkProductIdControl($params['productId'], $user);
		} elseif (@$params['target']) {
			$target = $params['target'];
			$productId = null;
			if ($target instanceof Product) {
				$productId = strval($target->id_ext);
			} elseif ($target instanceof Resource) {
				$productId = strval($target->product->id_ext);
			}
			if ($productId) {
				return $this->_checkProductIdControl($productId, $user);
			}
		}
		return false;
	}

	protected function _checkProductIdControl($productId, $userId) {
		$userModel = User::findOne($userId);
		if ($userModel) {
			$availableProducts = ProductAccessibilityHelper::getAvailableProducts($userModel);
			if ($availableProducts) {
				foreach ($availableProducts as $product) {
					if ($product['id'] === $productId) {
						return true;
					}
				}
			}
		}
		return false;
	}
}
