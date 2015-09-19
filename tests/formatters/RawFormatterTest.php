<?php

namespace tests\formatters;

use zhuravljov\yii\rest\formatters\RawFormatter;

/**
 * Class RawFormatterTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class RawFormatterTest extends FormatterTestCase
{
    /**
     * @inheritdoc
     */
    protected function getFormatterInstance()
    {
        return new RawFormatter();
    }

    public function testFormat()
    {
        $formatter = $this->getFormatterInstance();
        $record = $this->getResponseRecordInstance();
        $record->content = '12345';

        $this->assertEquals(
            '<pre><code id="response-content">12345</code></pre>',
            $formatter->format($record)
        );
    }
}