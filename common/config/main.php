<?php
return [
	'language' => 'ru',
	'sourceLanguage' => 'ru',

	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm' => '@vendor/npm-asset',
	],
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'components' => [
		'cache' => [
			'class' => \yii\caching\FileCache::class,
			'cachePath' => '@backend/runtime/cache',
		],
		'ad' => [
			'class' => \common\components\Adldap2Wrapper::class,
		],
		'authManager' => [
			'class' => \common\components\AuthManager::class,
			'cache' => 'cache',
			'defaultRoles' => [\common\data\RBACData::ROLE_DEFAULT],
		],
		'fileStorage' => [
			'class' => \yii2tech\filestorage\local\Storage::class,
			'basePath' => '@proot/storage',
			'baseUrl' => '/storage',
			'dirPermission' => 0755,
			'filePermission' => 0644,
		],
	],
];
