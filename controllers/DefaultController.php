<?php

namespace zhuravljov\yii\rest\controllers;

use Yii;
use yii\httpclient\Client;
use yii\web\Controller;
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
        $model->baseUrl = $this->module->baseUrl;

        if (
            $model->load(Yii::$app->request->post()) &&
            $model->validate()
        ) {
            $this->send($model);
            $tag = $this->module->storage->save($model);

            return $this->redirect(['index', 'tag' => $tag, '#' => 'response']);
        }

        $model->addNewParamRows();

        return $this->render('index', [
            'tag' => $tag,
            'model' => $model,
            'collection' => $this->module->storage->getCollectionGroups(),
            'history' => $this->module->storage->getHistory(),
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
        $url = $model->baseUrl . $model->endpoint;
        $params = [];
        foreach ($model->queryKeys as $i => $key) {
            if ($model->queryActives[$i]) {
                $params[] = $key . '=' . urlencode($model->queryValues[$i]);
            }
        }
        $url .= '?' . join('&', $params);

        $data = [];
        foreach ($model->bodyKeys as $i => $key) {
            if ($model->bodyActives[$i]) {
                $data[$key] = $model->bodyValues[$i];
            }
        }

        $headers = [];
        foreach ($model->headerKeys as $i => $key) {
            if ($model->headerActives[$i]) {
                $headers[$key] = $model->headerValues[$i];
            }
        }

        $client = new Client();
        $request = $client->createRequest()
            ->setMethod($model->method)
            ->setUrl($url)
            ->setData($data ?: null)
            ->setHeaders($headers ?: null);

        $begin = microtime(true);
        $response = $request->send();

        $data = [];
        $data['status'] = $response->getStatusCode();
        $data['time'] = microtime(true) - $begin;
        foreach ($response->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            $data['headers'][$name] = $values;
        }
        $data['content'] = $response->getContent();

        $model->response = $data;
    }
}
