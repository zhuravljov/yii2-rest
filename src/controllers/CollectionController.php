<?php

namespace zhuravljov\yii\rest\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use zhuravljov\yii\rest\models\ImportForm;

/**
 * Class CollectionController
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class CollectionController extends Controller
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
                    'link' => ['post'],
                    'unlink' => ['post'],
                ],
            ],
        ];
    }

    public function actionLink($tag)
    {
        if ($this->module->storage->addToCollection($tag)) {
            Yii::$app->session->setFlash('success', 'Request was added to collection successfully.');
            return $this->redirect(['request/create', 'tag' => $tag]);
        } else {
            throw new NotFoundHttpException('Request not found.');
        }
    }

    public function actionUnlink($tag)
    {
        if ($this->module->storage->removeFromCollection($tag)) {
            Yii::$app->session->setFlash('success', 'Request was removed from collection successfully.');
            return $this->redirect(['request/create']);
        } else {
            throw new NotFoundHttpException('Request not found.');
        }
    }

    public function actionExport()
    {
        return Yii::$app->response->sendContentAsFile(
            Json::encode($this->module->storage->exportCollection()),
            $this->module->id .'-' . date('Ymd-His') . '.json'
        );
    }

    public function actionImport()
    {
        $model = new ImportForm();
        if (
            $model->load(Yii::$app->request->post()) &&
            ($count = $model->save($this->module->storage)) !== false
        ) {
            if ($count) {
                Yii::$app->session->setFlash('success', "{$count} requests was imported to collection successfully.");
            } else {
                Yii::$app->session->setFlash('warning', "New requests not found.");
            }
            return $this->redirect(['request/create']);
        }
        return $this->render('import', [
            'model' => $model,
        ]);
    }
}