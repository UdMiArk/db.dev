<?php
$params = array_merge(
	require __DIR__ . '/../../common/config/params.php',
	require __DIR__ . '/../../common/config/params-local.php',
	require __DIR__ . '/params.php',
	require __DIR__ . '/params-local.php'
);

return [
	'id' => 'app-console',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
	'controllerNamespace' => 'console\controllers',
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm' => '@vendor/npm-asset',
	],
	'controllerMap' => [
		'migrate' => [
			'class' => \yii\console\controllers\MigrateController::class,
			'migrationPath' => [
				'@app/migrations',
				'@yii/rbac/migrations',
			],
			'migrationNamespaces' => [
				'yii\queue\db\migrations',
			],
		],
	],
	'components' => [
		'log' => [
			'targets' => [
				[
					'class' => \yii\log\FileTarget::class,
					'levels' => ['error', 'warning'],
				],
				[
					'class' => \yii\log\FileTarget::class,
					'categories' => ['application*'],
					'levels' => ['info'],
					'logFile' => '@runtime/logs/commands.log',
				],
				[
					'class' => \yii\log\FileTarget::class,
					'categories' => ['yii\queue\*'],
					'levels' => ['info', 'error', 'warning'],
					'logFile' => '@runtime/logs/queue.log',
				],
			],
		],
	],
	'params' => $params,
];
