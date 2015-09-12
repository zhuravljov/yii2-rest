<?php

namespace zhuravljov\yii\rest\formatters;

use yii\base\ErrorException;
use yii\helpers\Html;

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
    public function format($record)
    {
        $dom = new \DOMDocument();
        $dom->formatOutput = true;
        try {
            $dom->loadXML($record->content);
        } catch (ErrorException $e) {
            return $this->warn($e) . parent::format($record);
        }
        $content = $dom->saveXML();

        return Html::tag('pre',
            Html::tag('code',
                Html::encode($content),
                ['id' => 'response-content', 'class' => 'xml']
            )
        );
    }
}