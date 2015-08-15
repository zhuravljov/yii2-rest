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
 * @property \yii\httpclient\Client $client
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'zhuravljov\yii\rest\controllers';
    /**
     * @var array the list of IPs that are allowed to access this module.
     * Each array element represents a single IP filter which can be either an IP address
     * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
     * The default value is `['127.0.0.1', '::1']`, which means the module can only be accessed
     * by localhost.
     */
    public $allowedIPs = ['127.0.0.1', '::1'];
    /**
     * @var \zhuravljov\yii\rest\storages\Storage|array|string
     */
    private $_storage = 'zhuravljov\yii\rest\storages\FileStorage';
    /**
     * @var \yii\httpclient\Client|array
     */
    private $_client = [
        'class' => 'yii\httpclient\Client',
        'baseUrl' => null,
    ];

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $app->getUrlManager()->addRules([
                $this->id . '/<tag:[0-9a-f]+>' => $this->id . '/default/index',
                $this->id . '/<tag:[0-9a-f]+>/<action:[\w-]+>' => $this->id . '/default/<action>',
                $this->id => $this->id . '/default/index',
                $this->id . '/<action:[\w-]+>' => $this->id . '/default/<action>',
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

    /**
     * @param \yii\httpclient\Client|array $client
     */
    public function setClient($client)
    {
        $this->_client = $client;
    }

    /**
     * @return \yii\httpclient\Client
     */
    public function getClient()
    {
        if (!is_object($this->_client)) {
            $this->_client = Yii::createObject($this->_client);
        }

        return $this->_client;
    }
}
