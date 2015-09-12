<?php

namespace zhuravljov\yii\rest\formatters;

use yii\base\ErrorException;
use yii\helpers\Html;
use zhuravljov\yii\rest\HighlightAsset;

/**
 * Class XmlFormatter
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class XmlFormatter extends RawFormatter
{
    /**
     * @inheritdoc
     */
    public function format($record, $view)
    {
        $dom = new \DOMDocument();
        $dom->formatOutput = true;
        try {
            $dom->loadXML($record->content);
        } catch (ErrorException $e) {
            return $this->warn($e) . parent::format($record, $view);
        }
        $content = $dom->saveXML();

        HighlightAsset::register($view);
        $view->registerJs('hljs.highlightBlock(document.getElementById("response-content"));');
        $view->registerCss('pre code.hljs {background: transparent}');

        return Html::tag('pre',
            Html::tag('code',
                Html::encode($content),
                ['id' => 'response-content', 'class' => 'xml']
            )
        );
    }
}