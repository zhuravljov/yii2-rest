<?php

namespace zhuravljov\yii\rest;

use yii\web\AssetBundle;

/**
 * Class RestAsset
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class RestAsset extends AssetBundle
{
    public $sourcePath = '@zhuravljov/yii/rest/assets';
    public $css = [
        'main.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}