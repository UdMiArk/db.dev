<?php
return [
	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=127.0.0.1;dbname=some_database',
			'username' => 'some_database',
			'password' => '',
			'charset' => 'utf8',
		],
		'mailer' => [
			'class' => 'yii\swiftmailer\Mailer',
			'viewPath' => '@common/mail',

			'transport' => [
				'class' => 'Swift_SmtpTransport',
				'host' => 'smtp.gmail.com',
				'username' => 'some.email@fake-gmail.com',
				'password' => 'some.email.password',
				'port' => '587',
				'encryption' => 'tls',
			],
		],
	],
];
