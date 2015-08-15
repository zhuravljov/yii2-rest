<?php

namespace zhuravljov\yii\rest\controllers;

use Yii;
use yii\web\Controller;
use zhuravljov\yii\rest\helpers\ArrayHelper;
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
            $model = new RequestForm();
        } else {
            $model = $this->module->storage->find($tag);
        }
        $model->baseUrl = $this->module->client->baseUrl;

        if (
            $model->load(Yii::$app->request->post()) &&
            $model->validate()
        ) {
            $this->send($model);
            $tag = $this->module->storage->save($model);

            return $this->redirect(['index', 'tag' => $tag, '#' => 'response']);
        }

        $model->addNewParamRows();


        $history = $this->module->storage->getHistory();
        $collection = $this->module->storage->getCollection();

        foreach ($history as $tag => &$item) {
            $item['in_collection'] = isset($collection[$tag]);
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

        return $this->render('index', [
            'tag' => $tag,
            'model' => $model,
            'history' => $history,
            'collection' => $collection,
        ]);
    }

    public function actionRemoveFromHistory($tag)
    {
        $this->module->storage->find($tag);
        $this->module->storage->removeFromHistory($tag);

        return $this->redirect(['index']);
    }

    public function actionAddToCollection($tag)
    {
        $this->module->storage->find($tag);
        $this->module->storage->addToCollection($tag);

        return $this->redirect(['index', 'tag' => $tag]);
    }

    public function actionRemoveFromCollection($tag)
    {
        $this->module->storage->find($tag);
        $this->module->storage->removeFromCollection($tag);

        return $this->redirect(['index']);
    }

    /**
     * @param RequestForm $model
     */
    protected function send(RequestForm $model)
    {
        $request = $this->module->client->createRequest();
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

        $data = [];
        $data['duration'] = $duration;
        $data['status'] = $response->getStatusCode();
        foreach ($response->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            $data['headers'][$name] = $values;
        }
        $data['content'] = $response->getContent();

        $model->response = $data;
    }
}
