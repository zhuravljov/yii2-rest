<?php

namespace zhuravljov\yii\rest\formatters;

use yii\helpers\Html;
use zhuravljov\yii\rest\HighlightAsset;

/**
 * Class HtmlFormatter
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class HtmlFormatter extends RawFormatter
{
    /**
     * @inheritdoc
     */
    public function format($record, $view)
    {
        HighlightAsset::register($view);
        $view->registerJs('hljs.highlightBlock(document.getElementById("response-content"));');
        $view->registerCss('pre code.hljs {background: transparent}');

        return Html::tag('pre',
            Html::tag('code',
                Html::encode($record->content),
                ['id' => 'response-content', 'class' => 'html']
            )
        );
    }
}