<?php

namespace zhuravljov\yii\rest\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use zhuravljov\yii\rest\models\Sender;

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
            $model = new Sender();
        } else {
            $model = $this->findSender($tag);
        }

        if (
            $model->load(Yii::$app->request->post()) &&
            $model->validate()
        ) {
            $tag = $this->saveSender($model);
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
     * @return Sender
     * @throws NotFoundHttpException
     */
    protected function findSender($tag)
    {
        $path = Yii::getAlias($this->module->logPath . '/' . $this->module->id);
        $dataFileName = $path . "/{$tag}.data";
        
        if (file_exists($dataFileName)) {
            $data = unserialize(file_get_contents($dataFileName));

            return new Sender([
                'method' => ArrayHelper::remove($data, 'method'),
                'endpoint' => ArrayHelper::remove($data, 'endpoint'),
                'tab' => ArrayHelper::remove($data, 'tab', 1),
                'queryKeys' => ArrayHelper::remove($data, 'queryKeys', []),
                'queryValues' => ArrayHelper::remove($data, 'queryValues', []),
                'queryActives' => ArrayHelper::remove($data, 'queryActives', []),
                'bodyKeys' => ArrayHelper::remove($data, 'bodyKeys', []),
                'bodyValues' => ArrayHelper::remove($data, 'bodyValues', []),
                'bodyActives' => ArrayHelper::remove($data, 'bodyActives', []),
                'headerKeys' => ArrayHelper::remove($data, 'headerKeys', []),
                'headerValues' => ArrayHelper::remove($data, 'headerValues', []),
                'headerActives' => ArrayHelper::remove($data, 'headerActives', []),
            ]);
        } else {
            throw new NotFoundHttpException('Request not found.');
        }
    }

    /**
     * @param Sender $model
     * @return string
     */
    protected function saveSender(Sender $model)
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
        $data = [
            'time' => $time,
            'method' => $model->method,
            'endpoint' => $model->endpoint,
            'tab' => $model->tab,
            'queryKeys' => $model->queryKeys,
            'queryValues' => $model->queryValues,
            'queryActives' => $model->queryActives,
            'bodyKeys' => $model->bodyKeys,
            'bodyValues' => $model->bodyValues,
            'bodyActives' => $model->bodyActives,
            'headerKeys' => $model->headerKeys,
            'headerValues' => $model->headerValues,
            'headerActives' => $model->headerActives,
        ];

        FileHelper::createDirectory($path);
        file_put_contents($historyFileName, serialize($this->_history));
        file_put_contents($dataFileName, serialize($data));

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
