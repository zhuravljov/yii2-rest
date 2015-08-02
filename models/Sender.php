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

    public $paramKeys = [];
    public $paramValues = [];
    public $paramActives = [];

    public $dataKeys = [];
    public $dataValues = [];
    public $dataActives = [];

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
            [['paramKeys', 'dataKeys', 'headerKeys'], 'each', 'rule' => ['string']],
            [['paramValues', 'dataValues', 'headerValues'], 'each', 'rule' => ['string']],
            [['paramActives', 'dataActives', 'headerActives'], 'each', 'rule' => ['boolean']],
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
        $keys = (array)$this->paramKeys;
        $values = (array)$this->paramValues;
        $actives = (array)$this->paramActives;
        $this->paramKeys = [];
        $this->paramValues = [];
        $this->paramActives = [];
        while (
            ($key = each($keys)) &&
            ($value = each($values)) &&
            ($active = each($actives))
        ) {
            if ($key[1] === '' && $value[1] === '') continue;
            $this->paramKeys[] = $key[1];
            $this->paramValues[] = $value[1];
            $this->paramActives[] = $active[1];
        }

        $keys = (array)$this->dataKeys;
        $values = (array)$this->dataValues;
        $actives = (array)$this->dataActives;
        $this->dataKeys = [];
        $this->dataValues = [];
        $this->dataActives = [];
        while (
            ($key = each($keys)) &&
            ($value = each($values)) &&
            ($active = each($actives))
        ) {
            if ($key[1] === '' && $value[1] === '') continue;
            $this->dataKeys[] = $key[1];
            $this->dataValues[] = $value[1];
            $this->dataActives[] = $active[1];
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
        $this->paramKeys[] = '';
        $this->paramValues[] = '';
        $this->paramActives[] = true;

        $this->dataKeys[] = '';
        $this->dataValues[] = '';
        $this->dataActives[] = true;

        $this->headerKeys[] = '';
        $this->headerValues[] = '';
        $this->headerActives[] = true;
    }

    public function attributeLabels()
    {
        return [
            'endpoint' => 'endpoint',
            'paramKeys' => 'Query Param',
            'paramValues' => 'Value',
            'dataKeys' => 'Body Param',
            'dataValues' => 'Value',
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