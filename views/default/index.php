<?php
use yii\bootstrap\Nav;
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \zhuravljov\yii\rest\models\RequestForm $model
 * @var array $history
 */

$historyItems = [];
foreach (array_reverse($history, true) as $tag => $row) {
    $title = Html::encode(strtoupper($row['method'])) . ' ' . Html::encode($row['endpoint']);
    $class = 'color';
    if ($row['status'] < 300) {
        $class .= ' label-success';
    } elseif ($row['status'] < 400) {
        $class .= ' label-info';
    } elseif ($row['status'] < 500) {
        $class .= ' label-warning';
    } else {
        $class .= ' label-danger';
    }
    $label = $title . ' ' . Html::tag('span', '', ['class' => $class]);
    $historyItems[] = [
        'url' => ['default/index', 'tag' => $tag, '#' => str_replace(' ', '+', $title)],
        'label' => $label . Html::tag('small', \Yii::$app->formatter->asRelativeTime($row['time']), ['class' => 'pull-right']),
    ];
}
?>
<div class="rest-default-index">
    <div class="row">
        <div class="col-lg-9">

            <?= $this->render('_request', ['model' => $model]) ?>
            <?= $this->render('_response', ['data' => $model->response]) ?>

        </div>
        <div class="col-lg-3">

            <ul class="nav nav-tabs nav-justified">
                <li>
                    <a href="#collections" data-toggle="tab">
                        Collections
                    </a>
                </li>
                <li>
                    <a href="#history" data-toggle="tab">
                        History
                        <?= Html::tag('span', count($history), [
                            'class' => 'badge' . (!count($history) ? ' hidden' : '')
                        ]) ?>
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                <div id="collections" class="tab-pane">
                    TBD
                </div><!-- #collections -->

                <div id="history" class="tab-pane">
                    <?= Nav::widget([
                        'options' => ['class' => 'nav nav-pills nav-stacked'],
                        'encodeLabels' => false,
                        'items' => $historyItems,
                    ]) ?>
                </div><!-- #history -->

            </div>

        </div>
    </div>
</div>
<?php
$this->registerJs(<<<JS

if (window.localStorage) {
    var restHistoryTab = localStorage['restHistoryTab'] || 'collections';
    $('a[href=#' + restHistoryTab + ']').tab('show');
    $('a[href=#collections]').on('shown.bs.tab', function() {
        localStorage['restHistoryTab'] = 'collections';
    });
    $('a[href=#history]').on('shown.bs.tab', function() {
        localStorage['restHistoryTab'] = 'history';
    });
}

JS
);