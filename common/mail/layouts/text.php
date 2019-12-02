<?php

/** @var View $this view component instance */

use yii\mail\MessageInterface;
use yii\web\View;

/** @var MessageInterface $message the message being composed */
/** @var string $content main view render result */
$this->beginPage();
$this->beginBody();
print $content;
$this->endBody();
$this->endPage();
