<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


// require __DIR__ . '/BaseYii.php';
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'yiisoft' . DIRECTORY_SEPARATOR . 'yii2' . DIRECTORY_SEPARATOR . 'BaseYii.php';

/**
 * Class Yii
 *
 */
class Yii extends \yii\BaseYii {

}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'yiisoft' . DIRECTORY_SEPARATOR . 'yii2' . DIRECTORY_SEPARATOR . 'classes.php';
Yii::$container = new yii\di\Container();
