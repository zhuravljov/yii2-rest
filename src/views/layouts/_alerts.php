<?php
use yii\bootstrap\Alert;
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 */

foreach (Yii::$app->session->getAllFlashes(true) as $type => $message) {
    echo Alert::widget([
        'options' => ['class' => 'alert-' . $type],
        'body' => Html::encode($message),
    ]);
}