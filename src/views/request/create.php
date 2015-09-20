<?php
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var string $tag
 * @var string $baseUrl
 * @var \zhuravljov\yii\rest\models\RequestForm $model
 * @var \zhuravljov\yii\rest\models\ResponseRecord $record
 * @var array $history
 * @var array $collection
 */

if ($model->method) {
    $this->title = strtoupper($model->method) . ' ' . $model->endpoint;
} else {
    $this->title = 'New Request';
}
?>
<div class="rest-request-create">
    <div class="row">
        <div class="col-lg-9">

            <?= $this->render('_form', [
                'baseUrl' => $baseUrl,
                'model' => $model,
            ]) ?>
            <?= $this->render('_response', [
                'record' => $record,
            ]) ?>

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
$this->registerCss(<<<'CSS'

.tab-content {
    margin-top: 15px;
}
.counter:before { content: "("; }
.counter:after{ content: ")"; }

CSS
);
$this->registerCss(<<<'CSS'

.request-list,
.request-list ul {
    margin-bottom: 0;
    padding-left: 0;
    list-style: none;
}
.request-list  li {
    position: relative;
    display: block;
}
.request-list .request-list-group {
    padding: 10px 15px;
    margin-bottom: 5px;
    font-weight: bold;
    text-transform: uppercase;
    border-bottom: 2px solid #999;
}
.request-list li > a {
    position: relative;
    display: block;
    padding: 10px 15px;
    border-radius: 4px;
}
.request-list li > a:hover,
.request-list li > a:focus {
    text-decoration: none;
    background-color: #eee;
}
.request-list li.active > a {
    color: #fff;
    background-color: #337ab7;
}

.request-list li > .actions {
    display: none;
    position: absolute;
    top: 8px;
    right: 8px;
}
.request-list li:hover > .actions {
    display: inherit;
}
.request-list li > .actions > a {
    padding: 0 2px;
    font-size: 21px;
    font-weight: bold;
    line-height: 1;
    color: #000;
    text-decoration: none;
    opacity: .2;
}
.request-list li > .actions > a:hover {
    text-decoration: none;
    opacity: .5;
}

.request-list li > a:after {
    position: absolute;
    left: 0;
    top: 6px;
    bottom: 6px;
    width: 7px;
    border-right: 1px solid #fff;
    content: "";
}
.request-list  li.active > a:after,
.request-list li > a:hover:after,
.request-list li > a:focus:after {
    top: 0;
    bottom: 0;
    border-radius: 4px 0 0 4px;
}
.request-list li.success > a:after {
    background-color: #5cb85c;
}
.request-list li.info > a:after {
    background-color: #5bc0de;
}
.request-list li.warning > a:after {
    background-color: #f0ad4e;
}
.request-list li.danger > a:after {
    background-color: #d9534f;
}

.request-list .request-method {
    text-transform: uppercase;
    font-weight: bold;
}
.request-list .request-name {
    display: block;
    margin-right: 30px;
}
.request-list .request-description {
    display: block;
    font-size: 70%;
    color: #333;
}
.request-list .active .request-description {
    color: #fff;
}

CSS
);


