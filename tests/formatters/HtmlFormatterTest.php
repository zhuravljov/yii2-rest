<?php

namespace tests\formatters;

use yii\helpers\Html;
use zhuravljov\yii\rest\formatters\HtmlFormatter;
use zhuravljov\yii\rest\models\ResponseRecord;

/**
 * Class HtmlFormatterTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class HtmlFormatterTest extends FormatterTestCase
{
    public function testFormat()
    {
        $formatter = new HtmlFormatter();

        $expectedRecord = new ResponseRecord();
        $expectedRecord->content = '<div>12345</div>';
        $expectedHtml = Html::tag('pre',
            Html::tag('code',
                Html::encode($expectedRecord->content),
                ['id' => 'response-content', 'class' => 'html']
            )
        );
        $actualHtml = $formatter->format($expectedRecord);

        $this->assertEquals($expectedHtml, $actualHtml);
    }
}