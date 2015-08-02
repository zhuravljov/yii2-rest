<?php

namespace zhuravljov\yii\rest\controllers;

use yii\web\Controller;
use zhuravljov\yii\rest\models\Sender;

class DefaultController extends Controller
{
    public $layout = 'main';
    /**
     * @var \zhuravljov\yii\rest\Module
     */
    public $module;

    public function actionIndex()
    {
        $model = new Sender();
        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {

        }
        $model->addNewParamRows();
        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
