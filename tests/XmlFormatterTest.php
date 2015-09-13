<?php

namespace tests;

use zhuravljov\yii\rest\formatters\XmlFormatter;
use zhuravljov\yii\rest\models\ResponseRecord;

/**
 * Class XmlFormatterTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class XmlFormatterTest extends TestCase
{
    public function testFormat()
    {
        $formatter = new XmlFormatter();
        $record = new ResponseRecord();
        $record->content = '<?xml version="1.0" encoding="UTF-8"?><response><id>12345</id></response>';

        $this->assertEquals(<<<XML
<pre><code id="response-content" class="xml">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;
&lt;response&gt;
  &lt;id&gt;12345&lt;/id&gt;
&lt;/response&gt;
</code></pre>
XML
            , $formatter->format($record)
        );
    }
}