<?php

namespace zhuravljov\yii\rest\formatters;

use yii\base\Object;
use yii\helpers\Html;

/**
 * Class RawFormatter
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class RawFormatter extends Object
{
    /**
     * @param \zhuravljov\yii\rest\models\ResponseRecord $record
     * @param \yii\web\View $view
     * @return string
     */
    public function format($record, $view)
    {
        return Html::tag('pre',
            Html::tag('code',
                HTml::encode($record->content)
            )
        );
    }
}