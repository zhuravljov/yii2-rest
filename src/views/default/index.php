<?php
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var string $tag
 * @var \zhuravljov\yii\rest\models\RequestForm $model
 * @var array $history
 * @var array $collection
 */

if ($model->method) {
    $this->title = strtoupper($model->method) . ' ' . $model->endpoint;
} else {
    $this->title = 'New Request';
}
?>
<div class="rest-default-index">
    <div class="row">
        <div class="col-lg-9">

            <?= $this->render('_request', ['model' => $model]) ?>
            <?= $this->render('_response', ['data' => $model->response]) ?>

        </div>
        <div class="col-lg-3">

            <ul class="request-lists nav nav-tabs nav-justified">
                <li>
                    <a href="#collection" data-toggle="tab">
                        Collection
                        <?php
                        $count = array_reduce($collection, function ($sum, $rows) {
                            return $sum + count($rows);
                        }, 0);
                        echo Html::tag('span', $count, [
                            'class' => 'counter' . (!$count ? ' hidden' : '')
                        ]);
                        ?>
                    </a>
                </li>
                <li>
                    <a href="#history" data-toggle="tab">
                        History
                        <?= Html::tag('span', count($history), [
                            'class' => 'counter' . (!count($history) ? ' hidden' : '')
                        ]) ?>
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                <div class="form-group has-feedback">
                    <input id="history-search" type="text" class="form-control" placeholder="Search" />
                    <span class="glyphicon glyphicon-search form-control-feedback"></span>
                </div>

                <div id="collection" class="tab-pane">
                    <?= $this->render('_collection', [
                        'activeTag' => $tag,
                        'items' => $collection,
                    ]) ?>
                </div><!-- #collection -->

                <div id="history" class="tab-pane">
                    <?= $this->render('_history', [
                        'activeTag' => $tag,
                        'items' => $history,
                    ]) ?>
                </div><!-- #history -->

            </div>

        </div>
    </div>
</div>
<?php
$this->registerJs(<<<'JS'

if (window.localStorage) {
    var restHistoryTab = localStorage['restHistoryTab'] || 'collection';
    $('a[href=#' + restHistoryTab + ']').tab('show');
    $('a[href=#collection]').on('shown.bs.tab', function() {
        localStorage['restHistoryTab'] = 'collection';
    });
    $('a[href=#history]').on('shown.bs.tab', function() {
        localStorage['restHistoryTab'] = 'history';
    });
}

JS
);
$this->registerJs(<<<'JS'

$('.request-lists a[data-toggle=tab]').on('shown.bs.tab', function() {
    $('#history-search').focus();
});

$('#history-search').keyup(function() {
    var needle = $(this).val().toLowerCase();
    $('.request-list li > a > .request-name').each(function() {
        var item = $(this).parents('li').first();
        if ($(this).text().toLowerCase().indexOf(needle) >= 0) {
            item.show();
        } else {
            item.hide();
        }
    });
});

JS
);