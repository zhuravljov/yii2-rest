<?php

namespace zhuravljov\yii\rest\controllers;

use Yii;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use zhuravljov\yii\rest\models\RequestForm;

class DefaultController extends Controller
{
    public $layout = 'main';
    /**
     * @var \zhuravljov\yii\rest\Module
     */
    public $module;

    public function actionIndex($tag = null)
    {
        if ($tag === null) {
            $model = new RequestForm(['baseUrl' => $this->module->baseUrl]);
        } else {
            $model = $this->findModel($tag);
        }

        if (
            $model->load(Yii::$app->request->post()) &&
            $model->validate()
        ) {
            $tag = $this->saveModel($model);
            return $this->redirect(['index', 'tag' => $tag, '#' => 'response']);
        }

        $model->addNewParamRows();

        return $this->render('index', [
            'model' => $model,
            'history' => $this->loadHistory(),
        ]);
    }

    /**
     * @param string $tag
     * @return RequestForm
     * @throws NotFoundHttpException
     */
    protected function findModel($tag)
    {
        $path = Yii::getAlias($this->module->logPath . '/' . $this->module->id);
        $dataFileName = $path . "/{$tag}.data";
        
        if (file_exists($dataFileName)) {
            $model = new RequestForm(['baseUrl' => $this->module->baseUrl]);
            $model->setAttributes(unserialize(file_get_contents($dataFileName)));
            return $model;
        } else {
            throw new NotFoundHttpException('Request not found.');
        }
    }

    /**
     * @param RequestForm $model
     * @return string
     */
    protected function saveModel(RequestForm $model)
    {
        $tag = uniqid();
        $time = time();
        $path = Yii::getAlias($this->module->logPath . '/' . $this->module->id);
        $historyFileName = $path . '/history.data';
        $dataFileName = $path . "/{$tag}.data";

        $this->loadHistory();

        $this->_history[$tag] = [
            'time' => $time,
            'method' => $model->method,
            'endpoint' => $model->endpoint,
        ];
        FileHelper::createDirectory($path);
        file_put_contents($historyFileName, serialize($this->_history));
        file_put_contents($dataFileName, serialize($model->getAttributes(null, ['baseUrl'])));

        return $tag;
    }

    protected function loadHistory()
    {
        if ($this->_history !== null) return $this->_history;

        $path = Yii::getAlias($this->module->logPath . '/' . $this->module->id);
        $historyFileName = $path . '/history.data';

        $this->_history = [];
        if (file_exists($historyFileName)) {
            $this->_history = unserialize(file_get_contents($historyFileName));
        }

        return $this->_history;
    }

    private $_history;
}
