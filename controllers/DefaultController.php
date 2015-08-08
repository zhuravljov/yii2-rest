<?php

namespace zhuravljov\yii\rest\controllers;

use Yii;
use yii\helpers\FileHelper;
use yii\httpclient\Client;
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
            $model = $this->find($tag);
        }

        if (
            $model->load(Yii::$app->request->post()) &&
            $model->validate()
        ) {
            $response = $this->send($model);
            $tag = $this->save($model, $response);
            return $this->redirect(['index', 'tag' => $tag, '#' => 'response']);
        }

        $model->addNewParamRows();

        return $this->render('index', [
            'model' => $model,
            'history' => $this->getHistory(),
        ]);
    }

    /**
     * @return array
     */
    protected function getHistory()
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

    /**
     * @param string $tag
     * @return RequestForm
     * @throws NotFoundHttpException
     */
    protected function find($tag)
    {
        $path = Yii::getAlias($this->module->logPath . '/' . $this->module->id);
        $dataFileName = $path . "/{$tag}.data";

        if (file_exists($dataFileName)) {
            $model = new RequestForm(['baseUrl' => $this->module->baseUrl]);
            $data = unserialize(file_get_contents($dataFileName));
            $model->setAttributes($data['request']);
            $model->response = $data['response'];
            return $model;
        } else {
            throw new NotFoundHttpException('Request not found.');
        }
    }

    /**
     * @param RequestForm $model
     * @param array $response
     * @return string
     */
    protected function save(RequestForm $model, $response)
    {
        $tag = uniqid();
        $time = time();
        $path = Yii::getAlias($this->module->logPath . '/' . $this->module->id);
        $historyFileName = $path . '/history.data';
        $dataFileName = $path . "/{$tag}.data";

        $this->getHistory();

        $this->_history[$tag] = [
            'time' => $time,
            'method' => $model->method,
            'endpoint' => $model->endpoint,
        ];
        FileHelper::createDirectory($path);
        file_put_contents($historyFileName, serialize($this->_history));
        file_put_contents($dataFileName, serialize([
            'request' => $model->getAttributes(null, ['baseUrl', 'response']),
            'response' => $response,
        ]));

        return $tag;
    }

    protected function send(RequestForm $model)
    {
        $url = $model->baseUrl . $model->endpoint;
        $params = [];
        foreach ($model->queryKeys as $i => $key) {
            if ($model->queryActives[$i]) {
                $params[] = urlencode($key . '=' . $model->queryValues[$i]);
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

        return $data;
    }
}
