<?php /*@formatter:off*/
/**
 * @var \common\models\Resource $resource
 * @var \yii\base\View $this
 */

$product = $resource->product;
$type = $resource->type;
$market = $product->market;
?>
Внутренний ID: <?= $resource->primaryKey ?>

Последние изменение: <?= $resource->updated_at ?>


Название: <?= $resource->name ?>

Тип: <?= $type->name ?>

Объект продвижения: <?= $product->name ?>

Рынок: <?= $market->name ?>


Статус: <?= \common\data\EResourceStatus::getCaption($resource->status) ?>

<?php if ($resource->status_at) { ?>
Дата выставления статуса: <?= $resource->status_at ?>
<?php } ?>

Рынок: <?= $market->name ?>


Создатель: <?= $resource->user->name ?>

Создано: <?= $resource->created_at ?>
<?php if ($resource->archived_at) { ?>


В архиве с: <?= $resource->archived_at ?>

<?php if ($resource->archived_by) { ?>
Кто отправил в архив: <?= $resource->archivedBy->name ?>
<?php }} if ($resource->comment) { ?>


Комментарий: #############
	<?= implode("\n\t", explode("\n", $resource->comment)) ?>

##########################
<?php } /*@formatter:on*/ ?>
