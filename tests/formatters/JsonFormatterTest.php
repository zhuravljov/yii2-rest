<?php

namespace tests\formatters;

use yii\helpers\Html;
use yii\helpers\Json;
use zhuravljov\yii\rest\formatters\JsonFormatter;
use zhuravljov\yii\rest\models\ResponseRecord;

/**
 * Class JsonFormatterTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class JsonFormatterTest extends FormatterTestCase
{
    public function testFormat()
    {
        $formatter = new JsonFormatter();

        $expectedRecord = new ResponseRecord();
        $expectedRecord->content = '{"id":"1"}';
        $expectedData = Json::decode($expectedRecord->content);
        $expectedContent = Json::encode($expectedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $expectedHtml = Html::tag('pre',
            Html::tag('code',
                Html::encode($expectedContent),
                ['id' => 'response-content', 'class' => 'json']
            )
        );
        $actualHtml = $formatter->format($expectedRecord);

        $this->assertEquals($expectedHtml, $actualHtml);
    }

    public function testError()
    {
        $formatter = new JsonFormatter();

        $expectedRecord = new ResponseRecord();
        $expectedRecord->content = '{"id":"1"';
        $expectedHtml =
            Html::tag('div',
                '<strong>Warning!</strong> Syntax error.',
                ['class' => 'alert alert-warning']
            ) .
            Html::tag('pre',
                Html::tag('code',
                    Html::encode($expectedRecord->content),
                    ['id' => 'response-content']
                )
            );
        $actualHtml = $formatter->format($expectedRecord);

        $this->assertEquals($expectedHtml, $actualHtml);
    }
}