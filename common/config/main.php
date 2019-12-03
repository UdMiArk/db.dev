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
			'defaultProvider' => '@yar.owen',
			'providers' => [
				'@yar.owen' => [
					'autoconnect' => false,
					'config' => [
						'account_suffix' => '@yar.owen',
						'hosts' => ['10.2.1.4'],
						'base_dn' => 'dc=yar,dc=owen',

						//'username' => '',
						//'password' => '',

						//'port' => 636,
						//'use_ssl' => true,
						//'use_tls' => true,
					],
				],
			],
		],
		'authManager' => [
			'class' => \yii\rbac\DbManager::class,
			'cache' => 'cache',
		],
	],
];
