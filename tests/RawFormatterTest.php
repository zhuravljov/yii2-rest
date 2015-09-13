<?php

namespace tests;

use zhuravljov\yii\rest\formatters\RawFormatter;
use zhuravljov\yii\rest\models\ResponseRecord;

/**
 * Class RawFormatterTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class RawFormatterTest extends TestCase
{
    public function testFormat()
    {
        $formatter = new RawFormatter();
        $record = new ResponseRecord();
        $record->content = '12345';

        $this->assertEquals(
            '<pre><code id="response-content">12345</code></pre>',
            $formatter->format($record)
        );
    }
}