<?php

namespace tests\formatters;

use yii\helpers\Html;
use zhuravljov\yii\rest\formatters\XmlFormatter;
use zhuravljov\yii\rest\models\ResponseRecord;

/**
 * Class XmlFormatterTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class XmlFormatterTest extends FormatterTestCase
{
    public function testFormat()
    {
        $formatter = new XmlFormatter();

        $expectedRecord = new ResponseRecord();
        $expectedRecord->content = '<?xml version="1.0" encoding="UTF-8"?><response><id>12345</id></response>';
        $expectedDom = new \DOMDocument();
        $expectedDom->formatOutput = true;
        $expectedDom->loadXML($expectedRecord->content);
        $expectedContent = $expectedDom->saveXML();
        $expectedHtml = Html::tag('pre',
            Html::tag('code',
                Html::encode($expectedContent),
                ['id' => 'response-content', 'class' => 'xml']
            )
        );
        $actualHtml = $formatter->format($expectedRecord);

        $this->assertEquals($expectedHtml, $actualHtml);
    }
}