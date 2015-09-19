<?php

namespace tests\formatters;

use zhuravljov\yii\rest\formatters\RawFormatter;
use zhuravljov\yii\rest\models\ResponseRecord;

/**
 * Class RawFormatterTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class RawFormatterTest extends FormatterTestCase
{
    public function testFormat()
    {
        $formatter = new RawFormatter();

        $expectedRecord = new ResponseRecord();
        $expectedRecord->content = '12345';

        $this->assertEquals(
            '<pre><code id="response-content">12345</code></pre>',
            $formatter->format($expectedRecord)
        );
    }
}