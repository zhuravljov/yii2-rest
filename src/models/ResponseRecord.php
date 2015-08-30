<?php

namespace zhuravljov\yii\rest\models;

/**
 * Class ResponseRecord
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class ResponseRecord
{
    /**
     * @var integer
     */
    public $status;
    /**
     * @var float
     */
    public $duration;
    /**
     * @var array
     */
    public $headers = [];
    /**
     * @var string
     */
    public $content;
}