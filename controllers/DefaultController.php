<?php

namespace zhuravljov\yii\rest\controllers;

use Yii;
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
        ]);
    }

    /**
     * @param string $tag
     * @return Sender
     */
    protected function findSender($tag)
    {
        $path = Yii::getAlias($this->module->logPath . '/' . $this->module->id);
        $dataFileName = $path . "/{$tag}.data";
        
        if (file_exists($dataFileName)) {
            $data = unserialize(file_get_contents($dataFileName));
            return new Sender([
                'method' => $data['method'],
                'endpoint' => $data['endpoint'],
                'tab' => $data['tab'],
                'queryKeys' => $data['queryKeys'],
                'queryValues' => $data['queryValues'],
                'queryActives' => $data['queryActives'],
                'bodyKeys' => $data['bodyKeys'],
                'bodyValues' => $data['bodyValues'],
                'bodyActives' => $data['bodyActives'],
                'headerKeys' => $data['headerKeys'],
                'headerValues' => $data['headerValues'],
                'headerActives' => $data['headerActives'],
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

        $history = [];
        if (file_exists($historyFileName)) {
            $history = unserialize(file_get_contents($historyFileName));
        }

        $history[$tag] = [
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
        file_put_contents($historyFileName, serialize($history));
        file_put_contents($dataFileName, serialize($data));

        return $tag;
    }
}
