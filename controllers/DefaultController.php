<?php

namespace zhuravljov\yii\rest\controllers;

use yii\web\Controller;

class DefaultController extends Controller
{
    public $layout = 'main';
    /**
     * @var \zhuravljov\yii\rest\Module
     */
    public $module;

    public function actionIndex()
    {
        return $this->render('index');
    }
}
