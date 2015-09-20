<?php

namespace zhuravljov\yii\rest;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\web\Application;
use yii\web\ForbiddenHttpException;

/**
 * Class Module
 *
 * @property \zhuravljov\yii\rest\storages\Storage $storage
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @event models\RequestEvent an event raised before sending request.
     */
    const EVENT_ON_REQUEST = 'onRequest';
    /**
     * @event models\ResponseEvent an event raised after sending request and after obtaining response.
     */
    const EVENT_ON_RESPONSE = 'onResponse';

    /**
     * @inheritdoc
     */
    public $layout = 'one';
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'zhuravljov\yii\rest\controllers';
    /**
     * @inheritdoc
     */
    public $defaultRoute = 'request';
    /**
     * @var array the list of IPs that are allowed to access this module.
     * Each array element represents a single IP filter which can be either an IP address
     * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
     * The default value is `['127.0.0.1', '::1']`, which means the module can only be accessed
     * by localhost.
     */
    public $allowedIPs = ['127.0.0.1', '::1'];
    /**
     * @var string base request URL.
     */
    public $baseUrl;
    /**
     * @var array http client object configuration.
     */
    public $clientConfig = 'yii\httpclient\Client';
    /**
     * @var array
     */
    public $formatters = [
        'application/json' => 'zhuravljov\yii\rest\formatters\JsonFormatter',
        'application/xml' => 'zhuravljov\yii\rest\formatters\XmlFormatter',
        'text/html' => 'zhuravljov\yii\rest\formatters\HtmlFormatter',
    ];
    /**
     * @var \zhuravljov\yii\rest\storages\Storage|array|string
     */
    private $_storage = 'zhuravljov\yii\rest\storages\FileStorage';
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $app->getUrlManager()->addRules([
                $this->id . '/<tag:[0-9a-f]+>' => $this->id . '/request/create',
                $this->id => $this->id . '/request/create',
                $this->id . '/<controller:\w+>/<action:\w+>/<tag:[0-9a-f]+>' => $this->id . '/<controller>/<action>',
                $this->id . '/<controller:\w+>/<action:\w+>' => $this->id . '/<controller>/<action>',
            ], false);
        } else {
            throw new InvalidConfigException('Can use for web application only.');
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app instanceof Application && !$this->checkAccess()) {
            throw new ForbiddenHttpException('You are not allowed to access this page.');
        }

        $this->resetGlobalSettings();

        return true;
    }

    /**
     * Resets potentially incompatible global settings done in app config.
     */
    protected function resetGlobalSettings()
    {
        if (Yii::$app instanceof Application) {
            Yii::$app->assetManager->bundles = [];
        }
    }

    /**
     * @return boolean whether the module can be accessed by the current user
     */
    protected function checkAccess()
    {
        $ip = Yii::$app->getRequest()->getUserIP();
        foreach ($this->allowedIPs as $filter) {
            if (
                $filter === '*' || $filter === $ip ||
                (
                    ($pos = strpos($filter, '*')) !== false &&
                    !strncmp($ip, $filter, $pos)
                )
            ) {
                return true;
            }
        }
        Yii::warning(
            'Access to REST Client is denied due to IP address restriction. The requested IP is ' . $ip,
            __METHOD__
        );

        return false;
    }

    /**
     * @param \zhuravljov\yii\rest\storages\Storage|array|string $storage
     */
    public function setStorage($storage)
    {
        $this->_storage = $storage;
    }

    /**
     * @return \zhuravljov\yii\rest\storages\Storage
     */
    public function getStorage()
    {
        if (!is_object($this->_storage)) {
            $this->_storage = Yii::createObject($this->_storage, [$this]);
        }

        return $this->_storage;
    }
}
