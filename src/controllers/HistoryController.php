<?php

namespace zhuravljov\yii\rest\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class HistoryController
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class HistoryController extends Controller
{
    /**
     * @var \zhuravljov\yii\rest\Module
     */
    public $module;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'clear' => ['post'],
                ],
            ],
        ];
    }
    public function actionDelete($tag)
    {
        if ($this->module->storage->exists($tag)) {
            $this->module->storage->removeFromHistory($tag);
            return $this->redirect(['request/create']);
        } else {
            throw new NotFoundHttpException('Request not found.');
        }
    }

    public function actionClear()
    {
        $this->module->storage->clearHistory();
        return $this->redirect(['request/create']);
    }
}