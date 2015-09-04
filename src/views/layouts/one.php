<?php
/**
 * @var \yii\web\View $this
 * @var string $content
 */
?>
<?php $this->beginContent(__DIR__ . '/base.php', [
    'menu' => '',
]) ?>
    <?= $content ?>
<?php $this->endContent(); ?>