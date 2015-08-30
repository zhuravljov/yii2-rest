<?php

namespace zhuravljov\yii\rest\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use zhuravljov\yii\rest\helpers\ArrayHelper;
use zhuravljov\yii\rest\models\RequestForm;
use zhuravljov\yii\rest\models\ResponseRecord;

class DefaultController extends Controller
{
    /**
     * @var \zhuravljov\yii\rest\Module
     */
    public $module;
    /**
     * @inheritdoc
     */
    public $defaultAction = 'request';

    public function actionRequest($tag = null)
    {
        $model = new RequestForm(['baseUrl' => $this->module->baseUrl]);
        $record = new ResponseRecord();

        if (
            $tag !== null &&
            !$this->module->storage->load($tag, $model, $record)
        ) {
            throw new NotFoundHttpException('Request not found.');
        }

        if (
            $model->load(Yii::$app->request->post()) &&
            $model->validate()
        ) {
            $record = $this->send($model);
            $tag = $this->module->storage->save($model, $record);

            return $this->redirect(['request', 'tag' => $tag, '#' => 'response']);
        }

        $model->addNewParamRows();


        $history = $this->module->storage->getHistory();
        $collection = $this->module->storage->getCollection();

        foreach ($history as $_tag => &$item) {
            $item['in_collection'] = isset($collection[$_tag]);
        }
        unset($item);
        // TODO Grouping will move to the config level
        $collection = ArrayHelper::group($collection, function ($row) {
            if (preg_match('|[^/]+|', ltrim($row['endpoint'], '/'), $m)) {
                return $m[0];
            } else {
                return 'common';
            }
        });

        return $this->render('request', [
            'tag' => $tag,
            'model' => $model,
            'record' => $record,
            'history' => $history,
            'collection' => $collection,
        ]);
    }

    public function actionRemoveFromHistory($tag)
    {
        if ($this->module->storage->exists($tag)) {
            $this->module->storage->removeFromHistory($tag);

            return $this->redirect(['request']);
        } else {
            throw new NotFoundHttpException('Request not found.');
        }
    }

    public function actionAddToCollection($tag)
    {
        if ($this->module->storage->exists($tag)) {
            $this->module->storage->addToCollection($tag);

            return $this->redirect(['request', 'tag' => $tag]);
        } else {
            throw new NotFoundHttpException('Request not found.');
        }
    }

    public function actionRemoveFromCollection($tag)
    {
        if ($this->module->storage->exists($tag)) {
            $this->module->storage->removeFromCollection($tag);

            return $this->redirect(['request']);
        } else {
            throw new NotFoundHttpException('Request not found.');
        }
    }

    /**
     * @param RequestForm $model
     * @return ResponseRecord
     */
    protected function send(RequestForm $model)
    {
        /** @var \yii\httpclient\Client $client */
        $client = Yii::createObject($this->module->clientConfig, [
            'baseUrl' => $this->module->baseUrl,
        ]);

        $request = $client->createRequest();
        $request->setMethod($model->method);

        $uri = $model->endpoint;
        $params = [];
        foreach ($model->queryKeys as $i => $key) {
            if ($model->queryActives[$i]) {
                $params[] = $key . '=' . urlencode($model->queryValues[$i]);
            }
        }
        if ($params) {
            $uri .= '?' . join('&', $params);
        }
        $request->setUrl($uri);

        $data = [];
        foreach ($model->bodyKeys as $i => $key) {
            if ($model->bodyActives[$i]) {
                $data[$key] = $model->bodyValues[$i];
            }
        }
        $request->setData($data);

        $headers = [];
        foreach ($model->headerKeys as $i => $key) {
            if ($model->headerActives[$i]) {
                $headers[$key] = $model->headerValues[$i];
            }
        }
        $request->setHeaders($headers);

        $begin = microtime(true);
        $response = $request->send();
        $duration = microtime(true) - $begin;

        $record = new ResponseRecord();
        $record->status = $response->getStatusCode();
        $record->duration = $duration;
        foreach ($response->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            $record->headers[$name] = $values;
        }
        $record->content = $response->getContent();

        return $record;
    }
}
