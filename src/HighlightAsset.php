<?php

namespace zhuravljov\yii\rest;

use yii\web\AssetBundle;

/**
 * Class RestAsset
 *
 * @see https://github.com/isagalaev/highlight.js
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class HighlightAsset extends AssetBundle
{
    public $baseUrl = '//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.8.0';
    public $css = [
        'styles/default.min.css',
    ];
    public $js = [
        'highlight.min.js',
    ];
    public $depends = [
    ];
}