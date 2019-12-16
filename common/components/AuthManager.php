<?php


namespace common\components;

class AuthManager extends \yii\rbac\DbManager {
	const ROLE_SUPERUSER = \common\data\RBACData::ROLE_SUPERUSER;

	public function checkAccess($userId, $permissionName, $params = []) {
		if ($permissionName !== $this::ROLE_SUPERUSER && $this->checkAccess($userId, $this::ROLE_SUPERUSER)) {
			return true;
		}
		return parent::checkAccess($userId, $permissionName, $params);
	}

	public function getPermissionsNamesByUser($userId) {
		$permissions = array_keys($this->getPermissionsByUser($userId));

		$defaultPermissions = \Yii::$app->cache->get('defaultPermissions');
		if ($defaultPermissions === false) {
			$defaultPermissions = [];
			foreach ($this->getDefaultRoles() as $role) {
				$defaultPermissions = array_merge(
					$defaultPermissions,
					$this->getPermissionsByRole($role)
				);
			}
			$defaultPermissions = array_keys($defaultPermissions);
			\Yii::$app->cache->set('defaultPermissions', $defaultPermissions);
		}

		return array_values(array_unique(array_merge($permissions, $defaultPermissions)));
	}
}