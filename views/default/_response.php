<?php
use yii\helpers\Html;
use yii\web\Response;

/**
 * @var \yii\web\View $this
 * @var array $data
 */
?>
<div id="response" class="rest-response">
    <?php if ($data): ?>

        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#response-body" data-toggle="tab">
                    Response Body
                </a>
            </li>
            <li>
                <a href="#response-headers" data-toggle="tab">
                    Response Headers
                    <?= Html::tag('span', count($data['headers']), [
                        'class' => 'badge' . (!count($data['headers']) ? ' hidden' : '')
                    ]) ?>
                </a>
            </li>
            <li>
                <div class="info">
                    <strong>Status:</strong>
                    <span class="label <?= $data['status'] < 300 ? 'label-success' : 'label-danger' ?>">
                        <?= Html::encode($data['status']) ?>
                        <?= isset(Response::$httpStatuses[$data['status']]) ? Response::$httpStatuses[$data['status']] : '' ?>
                    </span>
                </div>
            </li>
            <li>
                <div class="info">
                    <strong>Time:</strong>
                    <span class="label label-default">
                        <?= round($data['time'] * 1000) ?> ms
                    </span>
                </div>
            </li>
        </ul>


        <div class="tab-content">

            <div id="response-body" class="tab-pane active">
                <pre><?= Html::encode($data['content']) ?></pre>
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
                        <?php foreach ($data['headers'] as $name => $values): ?>
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