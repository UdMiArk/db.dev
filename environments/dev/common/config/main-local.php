<?php
return [
	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=localhost;dbname=yii_template',
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
		],
		'mailer' => [
			'class' => 'yii\swiftmailer\Mailer',
			'viewPath' => '@common/mail',
			// send all mails to a file by default. You have to set
			// 'useFileTransport' to false and configure a transport
			// for the mailer to send real emails.
			'useFileTransport' => true,
		],
		'ad' => [
			'defaultProvider' => '@db.dev',
			'providers' => [
				'@db.dev' => [
					'autoconnect' => false,
					'config' => [
						'account_suffix' => '@db.dev',
						'hosts' => ['db.dev'],
						'base_dn' => 'dc=db,dc=dev',

						//'username' => '',
						//'password' => '',

						//'port' => 636,
						//'use_ssl' => true,
						//'use_tls' => true,
					],
				],
			],
		],
	],
];
