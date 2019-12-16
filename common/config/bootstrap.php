<?php
Yii::setAlias('@proot', dirname(dirname(__DIR__)));
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
if (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'bootstrap-local.php')) {
	/** @noinspection PhpIncludeInspection */
	require(__DIR__ . DIRECTORY_SEPARATOR . 'bootstrap-local.php');
}