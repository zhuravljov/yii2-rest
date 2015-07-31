<?php

namespace zhuravljov\yii\rest;

use yii\base\BootstrapInterface;
use yii\web\Application;

class Module extends \yii\base\Module implements BootstrapInterface
{
    public $controllerNamespace = 'zhuravljov\yii\rest\controllers';

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $app->getUrlManager()->addRules([
                $this->id => $this->id . '/default/index',
            ], false);
        }
    }
}
