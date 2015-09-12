<?php

namespace zhuravljov\yii\rest\models;

use yii\base\Model;
use yii\validators\Validator;

/**
 * Class RequestForm
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class RequestForm extends Model
{
    public $method;
    public $endpoint;

    public $tab = 1;

    public $queryKeys = [];
    public $queryValues = [];
    public $queryActives = [];

    public $bodyKeys = [];
    public $bodyValues = [];
    public $bodyActives = [];

    public $headerKeys = [];
    public $headerValues = [];
    public $headerActives = [];

    public $description;

    public function rules()
    {
        return [
            ['method', 'required'],
            ['method', 'in', 'range' => array_keys(static::methodLabels())],

            ['endpoint', 'string'],
            ['endpoint', 'validateEndpoint'],

            ['tab', 'in', 'range' => [1, 2, 3]],

            [['queryKeys', 'bodyKeys', 'headerKeys'], 'each', 'rule' => ['string']],
            ['headerKeys', 'each', 'rule' => ['trim']],
            [['queryValues', 'bodyValues', 'headerValues'], 'each', 'rule' => ['string']],
            [['queryActives', 'bodyActives', 'headerActives'], 'each', 'rule' => ['boolean']],

            ['description', 'string'],
        ];
    }

    public function validateEndpoint()
    {
        $url = 'http://example.com/' . $this->endpoint;
        $validator = Validator::createValidator('url', $this, []);
        if ($validator->validate($url, $error)) {
            // Crop fragment
            if (($pos = strpos($this->endpoint, '#')) !== false) {
                $this->endpoint = substr($this->endpoint, 0, $pos);
            }
            // Crop query
            if (($pos = strpos($this->endpoint, '?')) !== false) {
                $this->endpoint = substr($this->endpoint, 0, $pos);
            }
            // Convert query string into params
            $query = trim(parse_url($url, PHP_URL_QUERY));
            $this->parseQuery($query, $this->queryKeys, $this->queryValues, $this->queryActives);
        } else {
            $this->addError('endpoint', $error);
        }
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->prepareRows($this->queryKeys, $this->queryValues, $this->queryActives);
            $this->prepareRows($this->bodyKeys, $this->bodyValues, $this->bodyActives);
            $this->prepareRows($this->headerKeys, $this->headerValues, $this->headerActives);

            return true;
        } else {
            return false;
        }
    }

    public function addEmptyRows()
    {
        $this->addEmptyRow($this->queryKeys, $this->queryValues, $this->queryActives);
        $this->addEmptyRow($this->bodyKeys, $this->bodyValues, $this->bodyActives);
        $this->addEmptyRow($this->headerKeys, $this->headerValues, $this->headerActives);
    }

    public function attributeLabels()
    {
        return [
            'endpoint' => 'endpoint',
            'queryKeys' => 'Query Param',
            'queryValues' => 'Value',
            'bodyKeys' => 'Body Param',
            'bodyValues' => 'Value',
            'headerKeys' => 'Header',
            'headerValues' => 'Value',
            'description' => 'Description',
        ];
    }

    public static function methodLabels()
    {
        return [
            'get' => 'GET',
            'post' => 'POST',
            'put' => 'PUT',
            'patch' => 'PATCH',
            'delete' => 'DELETE',
            'head' => 'HEAD',
            'options' => 'OPTIONS',
        ];
    }

    /**
     * @return string
     */
    public function getUri()
    {
        if (($query = $this->getQueryString()) !== '') {
            return  $this->endpoint . '?' . $query;
        } else {
            return  $this->endpoint;
        }
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        $this->buildQuery($this->queryKeys, $this->queryValues, $this->queryActives, $query);
        return $query;
    }

    /**
     * @return array
     */
    public function getBodyParams()
    {
        $this->buildQuery($this->bodyKeys, $this->bodyValues, $this->bodyActives, $query);
        parse_str($query, $data);

        return $data;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        $headers = [];
        foreach ($this->headerKeys as $i => $key) {
            if ($this->headerActives[$i]) {
                $headers[$key][] = $this->headerValues[$i];
            }
        }

        return $headers;
    }

    /**
     * @param string[] $keys
     * @param string[] $values
     * @param boolean[] $actives
     */
    private function prepareRows(&$keys, &$values, &$actives)
    {
        $k = (array)$keys;
        $v = (array)$values;
        $a = (array)$actives;
        $keys = [];
        $values = [];
        $actives = [];
        while(($key = each($k)) && ($value = each($v)) && ($active = each($a))) {
            if ($key[1] !== '' || $value[1] !== '') {
                $keys[] = $key[1];
                $values[] = $value[1];
                $actives[] = $active[1];
            }
        }
    }

    /**
     * @param string[] $keys
     * @param string[] $values
     * @param boolean[] $actives
     */
    private function addEmptyRow(&$keys, &$values, &$actives)
    {
        $keys[] = '';
        $values[] = '';
        $actives[] = true;
    }

    /**
     * @param string $query
     * @param string[] $keys
     * @param string[] $values
     * @param boolean[] $actives
     * @return bool
     */
    private function parseQuery($query, &$keys, &$values, &$actives)
    {
        if ($query !== '') {
            foreach (explode('&', $query) as $couple) {
                list($key, $value) = explode('=', $couple, 2) + [1 => ''];
                $keys[] = urldecode($key);
                $values[] = urldecode($value);
                $actives[] = true;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string[] $keys
     * @param string[] $values
     * @param boolean[] $actives
     * @param string $query
     * @return bool
     */
    private function buildQuery($keys, $values, $actives, &$query)
    {
        $couples = [];
        foreach ($keys as $i => $key) {
            if ($actives[$i]) {
                $couples[] =
                    str_replace(['%5B', '%5D'], ['[', ']'], urlencode($key)) .
                    '=' .
                    urlencode($values[$i]);
            }
        }
        $query = join('&', $couples);

        return $query !== '';
    }
}