<?php

namespace tests\formatters;

use tests\TestCase;
use zhuravljov\yii\rest\formatters\RawFormatter;
use zhuravljov\yii\rest\models\ResponseRecord;

/**
 * Class FormatterTestCase
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
abstract class FormatterTestCase extends TestCase
{
    /**
     * @return RawFormatter
     */
    abstract protected function getFormatterInstance();

    /**
     * @return ResponseRecord
     */
    protected function getResponseRecordInstance()
    {
        return new ResponseRecord();
    }
}