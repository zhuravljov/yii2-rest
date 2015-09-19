<?php

namespace tests\formatters;

use zhuravljov\yii\rest\formatters\XmlFormatter;

/**
 * Class XmlFormatterTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class XmlFormatterTest extends FormatterTestCase
{
    /**
     * @inheritdoc
     */
    protected function getFormatterInstance()
    {
        return new XmlFormatter();
    }

    public function testFormat()
    {
        $formatter = $this->getFormatterInstance();
        $record = $this->getResponseRecordInstance();
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