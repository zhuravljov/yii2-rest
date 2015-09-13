<?php

namespace tests;

use zhuravljov\yii\rest\formatters\HtmlFormatter;
use zhuravljov\yii\rest\models\ResponseRecord;

/**
 * Class HtmlFormatterTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class HtmlFormatterTest extends TestCase
{
    public function testFormat()
    {
        $formatter = new HtmlFormatter();
        $record = new ResponseRecord();
        $record->content = '<div>12345</div>';

        $this->assertEquals(
            '<pre><code id="response-content" class="html">&lt;div&gt;12345&lt;/div&gt;</code></pre>',
            $formatter->format($record)
        );
    }
}