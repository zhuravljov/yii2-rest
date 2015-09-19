<?php

namespace tests\formatters;

use zhuravljov\yii\rest\formatters\HtmlFormatter;

/**
 * Class HtmlFormatterTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class HtmlFormatterTest extends FormatterTestCase
{
    /**
     * @inheritdoc
     */
    protected function getFormatterInstance()
    {
        return new HtmlFormatter();
    }

    public function testFormat()
    {
        $formatter = $this->getFormatterInstance();
        $record = $this->getResponseRecordInstance();
        $record->content = '<div>12345</div>';

        $this->assertEquals(
            '<pre><code id="response-content" class="html">&lt;div&gt;12345&lt;/div&gt;</code></pre>',
            $formatter->format($record)
        );
    }
}