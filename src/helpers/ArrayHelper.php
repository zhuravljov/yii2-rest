<?php

namespace zhuravljov\yii\rest\helpers;

use yii\helpers\BaseArrayHelper;

/**
 * Class ArrayHelper
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class ArrayHelper extends BaseArrayHelper
{
    /**
     * @param array $array
     * @param string|\Closure $key
     * @param mixed $default
     * @return array
     */
    public static function group($array, $key, $default = null)
    {
        $result = [];
        foreach ($array as $k => $element) {
            $result[static::getValue($element, $key, $default)][$k] = $element;
        }

        return $result;
    }
}