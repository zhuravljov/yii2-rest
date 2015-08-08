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
    public $baseUrl;

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

    public $response = [];

    public function rules()
    {
        return [
            ['method', 'required'],
            ['method', 'in', 'range' => array_keys($this->methodLabels())],

            ['endpoint', 'string'],
            ['endpoint', 'validateEndpoint'],
            ['endpoint', 'required'],

            ['tab', 'in', 'range' => [1, 2, 3]],

            [['queryKeys', 'bodyKeys', 'headerKeys'], 'each', 'rule' => ['string']],
            [['queryValues', 'bodyValues', 'headerValues'], 'each', 'rule' => ['string']],
            [['queryActives', 'bodyActives', 'headerActives'], 'each', 'rule' => ['boolean']],
        ];
    }

    public function validateEndpoint()
    {
        $url = $this->baseUrl . $this->endpoint;
        $validator = Validator::createValidator('url', $this, []);
        if ($validator->validate($url, $error)) {
            // Crop fragment
            if (($pos = strpos($this->endpoint, '#')) !== false) {
                $this->endpoint = substr($this->endpoint, 0 , $pos);
            }
            // Crop query
            if (($pos = strpos($this->endpoint, '?')) !== false) {
                $this->endpoint = substr($this->endpoint, 0 , $pos);
            }
            // Parse params
            $query = parse_url($url, PHP_URL_QUERY);
            if (trim($query) !== '') {
                foreach (explode('&', $query) as $couple) {
                    list($key, $value) = explode('=', $couple, 2) + [1 => ''];
                    $this->queryKeys[] = urldecode($key);
                    $this->queryValues[] = urldecode($value);
                    $this->queryActives[] = true;
                }
            }
        } else {
            $this->addError('endpoint', $error);
        }
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->beforeValidateParamRows();
            return true;
        } else {
            return false;
        }
    }

    protected function beforeValidateParamRows()
    {
        $keys = (array)$this->queryKeys;
        $values = (array)$this->queryValues;
        $actives = (array)$this->queryActives;
        $this->queryKeys = [];
        $this->queryValues = [];
        $this->queryActives = [];
        while (
            ($key = each($keys)) &&
            ($value = each($values)) &&
            ($active = each($actives))
        ) {
            if ($key[1] === '' && $value[1] === '') continue;
            $this->queryKeys[] = $key[1];
            $this->queryValues[] = $value[1];
            $this->queryActives[] = $active[1];
        }

        $keys = (array)$this->bodyKeys;
        $values = (array)$this->bodyValues;
        $actives = (array)$this->bodyActives;
        $this->bodyKeys = [];
        $this->bodyValues = [];
        $this->bodyActives = [];
        while (
            ($key = each($keys)) &&
            ($value = each($values)) &&
            ($active = each($actives))
        ) {
            if ($key[1] === '' && $value[1] === '') continue;
            $this->bodyKeys[] = $key[1];
            $this->bodyValues[] = $value[1];
            $this->bodyActives[] = $active[1];
        }

        $keys = (array)$this->headerKeys;
        $values = (array)$this->headerValues;
        $actives = (array)$this->headerActives;
        $this->headerKeys = [];
        $this->headerValues = [];
        $this->headerActives = [];
        while (
            ($key = each($keys)) &&
            ($value = each($values)) &&
            ($active = each($actives))
        ) {
            if ($key[1] === '' && $value[1] === '') continue;
            $this->headerKeys[] = $key[1];
            $this->headerValues[] = $value[1];
            $this->headerActives[] = $active[1];
        }
    }

    public function addNewParamRows()
    {
        $this->queryKeys[] = '';
        $this->queryValues[] = '';
        $this->queryActives[] = true;

        $this->bodyKeys[] = '';
        $this->bodyValues[] = '';
        $this->bodyActives[] = true;

        $this->headerKeys[] = '';
        $this->headerValues[] = '';
        $this->headerActives[] = true;
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
        ];
    }

    public function methodLabels()
    {
        return [
            'get' => 'GET',
            'post' => 'POST',
            'put' => 'PUT',
            'delete' => 'DELETE',
        ];
    }
}