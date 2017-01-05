<?php
use yii\bootstrap\Nav;
use zhuravljov\yii\rest\Module;

/**
 * @var \yii\web\View $this
 * @var string $content
 */

$items = [];
foreach (Yii::$app->getModules(true) as $module) {
    if ($module instanceof Module) {
        $items[] = [
            'label' => $module->name,
            'url' => ['/' . $module->id],
            'active' => $module === Yii::$app->controller->module,
        ];
    }
}
?>
<?php $this->beginContent(__DIR__ . '/base.php', [
    'menu' => Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => [
            [
                'label' => 'Clients',
                'items' => $items,
            ],
        ],
    ]),
]) ?>
    <?= $content ?>
<?php $this->endContent(); ?>