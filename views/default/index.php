<?php
use yii\bootstrap\Nav;
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \zhuravljov\yii\rest\models\Sender $model
 * @var array $history
 */

$historyItems = [];
foreach (array_reverse($history, true) as $tag => $row) {
    $historyItems[] = [
        'url' => ['index', 'tag' => $tag],
        'label' =>
            Html::tag('small', \Yii::$app->formatter->asRelativeTime($row['time']), ['class' => 'pull-right']) .
            Html::tag('span', Html::encode($row['method']), ['class' => 'text-uppercase']) .
            ' ' .
            Html::encode($row['endpoint']),
    ];
}
?>
<div class="rest-default-index">
    <div class="row">
        <div class="col-lg-9">

            <?= $this->render('_form', ['model' => $model]) ?>

        </div>
        <div class="col-lg-3">

            <ul class="nav nav-tabs nav-justified">
                <li class="active">
                    <a href="#history" data-toggle="tab">History</a>
                </li>
                <li>
                    <a href="#collections" data-toggle="tab">Collections</a>
                </li>
            </ul>

            <div class="tab-content">

                <div id="history" class="tab-pane active">
                    <?= Nav::widget([
                        'options' => ['class' => 'nav nav-pills nav-stacked'],
                        'encodeLabels' => false,
                        'items' => $historyItems,
                    ]) ?>
                </div><!-- #history -->

                <div id="collections" class="tab-pane">
                    collections
                </div><!-- #collections -->

            </div>

        </div>
    </div>
</div>
