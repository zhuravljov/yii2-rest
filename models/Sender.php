<?php

namespace zhuravljov\yii\rest\models;

use yii\base\Model;

/**
 * Class Sender
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class Sender extends Model
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

    public function rules()
    {
        return [
            [['method', 'endpoint'], 'required'],
            ['method', 'in', 'range' => array_keys($this->methodLabels())],
            ['endpoint', 'string'],
            ['tab', 'in', 'range' => [1, 2, 3]],
            [['queryKeys', 'bodyKeys', 'headerKeys'], 'each', 'rule' => ['string']],
            [['queryValues', 'bodyValues', 'headerValues'], 'each', 'rule' => ['string']],
            [['queryActives', 'bodyActives', 'headerActives'], 'each', 'rule' => ['boolean']],
        ];
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