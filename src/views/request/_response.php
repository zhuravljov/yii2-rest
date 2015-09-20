<?php
use yii\helpers\Html;
use yii\web\Response;

/**
 * @var \yii\web\View $this
 * @var \zhuravljov\yii\rest\models\ResponseRecord $record
 */
?>
<div id="response" class="rest-request-response">
    <?php if ($record->status): ?>

        <ul class="nav nav-tabs">
            <li>
                <a href="#response-body" data-toggle="tab">
                    Response Body
                </a>
            </li>
            <li>
                <a href="#response-headers" data-toggle="tab">
                    Response Headers
                    <?= Html::tag('span', count($record->headers), [
                        'class' => 'counter' . (!count($record->headers) ? ' hidden' : '')
                    ]) ?>
                </a>
            </li>
            <li class="pull-right">
                <div class="info">
                    Duration:
                    <span class="label label-default">
                        <?= round($record->duration * 1000) ?> ms
                    </span>
                </div>
            </li>
            <li class="pull-right">
                <div class="info">
                    Status:
                    <?php
                    $class = 'label';
                    if ($record->status < 300) {
                        $class .= ' label-success';
                    } elseif ($record->status < 400) {
                        $class .= ' label-info';
                    } elseif ($record->status < 500) {
                        $class .= ' label-warning';
                    } else {
                        $class .= ' label-danger';
                    }
                    ?>
                    <span class="<?= $class ?>">
                        <?= Html::encode($record->status) ?>
                        <?= isset(Response::$httpStatuses[$record->status]) ? Response::$httpStatuses[$record->status] : '' ?>
                    </span>
                </div>
            </li>
        </ul>


        <div class="tab-content">

            <div id="response-body" class="tab-pane">
                <?php
                $contentType = !empty($record->headers['Content-Type']) ? $record->headers['Content-Type'][0] : '';
                $formatterConfig = 'zhuravljov\yii\rest\formatters\RawFormatter';
                foreach ($this->context->module->formatters as $mimeType => $config) {
                    if (strpos($contentType, $mimeType) === 0) {
                        $formatterConfig = $config;
                        break;
                    }
                }
                /** @var \zhuravljov\yii\rest\formatters\RawFormatter $formatter */
                $formatter = \Yii::createObject($formatterConfig);
                echo $formatter->format($record, $this);
                \zhuravljov\yii\rest\HighlightAsset::register($this);
                $this->registerJs('hljs.highlightBlock(document.getElementById("response-content"));');
                $this->registerCss('pre code.hljs {background: transparent}');
                ?>
            </div><!-- #response-body -->

            <div id="response-headers" class="tab-pane">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($record->headers as $name => $values): ?>
                            <?php foreach ($values as $value): ?>
                                <tr>
                                    <th><?= Html::encode($name) ?></th>
                                    <td><samp><?= Html::encode($value) ?></samp></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php  ?>
            </div><!-- #response-headers -->

        </div>
    <?php endif; ?>
</div>
<?php
$this->registerJs(<<<JS

if (window.localStorage) {
    var responseTab = localStorage['responseTab'] || 'response-body';
    $('a[href=#' + responseTab + ']').tab('show');
    $('a[href=#response-body]').on('shown.bs.tab', function() {
        localStorage['responseTab'] = 'response-body';
    });
    $('a[href=#response-headers]').on('shown.bs.tab', function() {
        localStorage['responseTab'] = 'response-headers';
    });
}

JS
);
$this->registerCss(<<<'CSS'

.nav-tabs > li > .info {
    position: relative;
    display: block;
    padding: 10px 0 10px 15px;
    font-weight: bold;
}
.nav-tabs > li > .info .label {
    white-space: normal;
    font-size: 85%;
}
#response-headers tbody td {
    word-break: break-all;
}
#response-headers tbody th {
    width: 30%;
}
CSS
);