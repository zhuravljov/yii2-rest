<?php

namespace tests;

use zhuravljov\yii\rest\formatters\JsonFormatter;
use zhuravljov\yii\rest\models\ResponseRecord;

/**
 * Class JsonFormatterTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class JsonFormatterTest extends TestCase
{
    public function testFormat()
    {
        $formatter = new JsonFormatter();
        $record = new ResponseRecord();
        $record->content = '{"id":"1"}';

        $this->assertEquals(<<<HTML
<pre><code id="response-content" class="json">{
    &quot;id&quot;: &quot;1&quot;
}</code></pre>
HTML
            , $formatter->format($record)
        );
    }

    public function testError()
    {
        $formatter = new JsonFormatter();
        $record = new ResponseRecord();
        $record->content = '{"id":"1"';

        $this->assertEquals(
            '<div class="alert alert-warning"><strong>Warning!</strong> Syntax error.</div>' .
            '<pre><code id="response-content">{&quot;id&quot;:&quot;1&quot;</code></pre>',
            $formatter->format($record)
        );
    }
}