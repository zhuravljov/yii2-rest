<?php

namespace zhuravljov\yii\rest\storages;

use Yii;
use yii\base\Object;
use zhuravljov\yii\rest\models\RequestForm;
use zhuravljov\yii\rest\models\ResponseRecord;

/**
 * Class Storage
 *
 * @property \zhuravljov\yii\rest\Module $module
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
abstract class Storage extends Object
{
    /**
     * @var \zhuravljov\yii\rest\Module
     */
    private $_module;
    /**
     * @var array
     */
    private $_history;
    /**
     * @var array
     */
    private $_collection;

    /**
     * @param \zhuravljov\yii\rest\Module $module
     * @param array $config
     */
    public function __construct($module, $config = [])
    {
        $this->_module = $module;
        parent::__construct($config);
    }

    /**
     * @return \zhuravljov\yii\rest\Module
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * @param string $tag
     * @return boolean
     */
    public function exists($tag)
    {
        return $this->readData($tag, $request, $response);
    }

    /**
     * @param string $tag
     * @param RequestForm $model
     * @param ResponseRecord $record
     * @return boolean
     */
    public function load($tag, RequestForm $model, ResponseRecord $record)
    {
        if ($this->readData($tag, $request, $response)) {
            $model->setAttributes($request);
            $record->status = $response['status'];
            $record->duration = $response['duration'];
            $record->headers = $response['headers'];
            $record->content = $response['content'];

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param RequestForm $model
     * @param ResponseRecord $record
     * @return string
     */
    public function save(RequestForm $model, ResponseRecord $record)
    {
        $tag = uniqid();
        $this->writeData($tag, $model->getAttributes(), get_object_vars($record));
        $this->addToHistory($tag, [
            'method' => $model->method,
            'endpoint' => $model->endpoint,
            'description' => $model->description,
            'status' => $record->status,
            'time' => time(),
        ]);

        return $tag;
    }


    /**
     * @param null|string $tag
     * @param mixed $default
     * @return array|mixed
     */
    public function getHistory($tag = null, $default = null)
    {
        if ($this->_history === null) {
            $this->_history = $this->readHistory();
        }
        if ($tag === null) {
            return $this->_history;
        } elseif (isset($this->_history[$tag])) {
            return $this->_history[$tag];
        } else {
            return $default;
        }
    }

    /**
     * @param null|string $tag
     * @param mixed $default
     * @return array|mixed
     */
    public function getCollection($tag = null, $default = null)
    {
        if ($this->_collection === null) {
            $this->_collection = $this->readCollection();
            uasort($this->_collection, [$this, 'compareCollection']);
        }
        if ($tag === null) {
            return $this->_collection;
        } elseif (isset($this->_collection[$tag])) {
            return $this->_collection[$tag];
        } else {
            return $default;
        }
    }

    private function compareCollection($row1, $row2)
    {
        $methods = array_keys(RequestForm::methodLabels());
        if ($result = strcmp($row1['endpoint'], $row2['endpoint'])) {
            return $result; // 2. Order by endpoints
        }
        elseif ($result = array_search($row1['method'], $methods) - array_search($row2['method'], $methods)) {
            return $result; // 2. Order by methods
        }
        else {
            return $row1['time'] - $row2['time']; // 3. Order by time
        }
    }

    /**
     * @param string $tag
     * @param array $data
     * @return boolean
     */
    public function addToHistory($tag, array $data)
    {
        $this->getHistory();
        $this->_history[$tag] = $data;
        $this->writeHistory($this->_history);

        return true;
    }

    /**
     * @param string $tag
     * @return boolean
     */
    public function removeFromHistory($tag)
    {
        $this->getHistory();
        if ($result = isset($this->_history[$tag])) {
            unset($this->_history[$tag]);
            $this->writeHistory($this->_history);
        }
        if (!$this->getCollection($tag)) {
            $this->removeData($tag);
        }

        return $result;
    }

    /**
     * @param string $tag
     * @return boolean
     */
    public function addToCollection($tag)
    {
        if ($data = $this->getHistory($tag)) {
            $data['time'] = time();
            $this->getCollection();
            $this->_collection[$tag] = $data;
            $this->writeCollection($this->_collection);

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $tag
     * @return boolean
     */
    public function removeFromCollection($tag)
    {
        $this->getCollection();
        if ($result = isset($this->_collection[$tag])) {
            unset($this->_collection[$tag]);
            $this->writeCollection($this->_collection);
        }
        if (!$this->getHistory($tag)) {
            $this->removeData($tag);
        }

        return $result;
    }

    /**
     * @param string $tag
     * @param array $request
     * @param array $response
     * @return boolean
     */
    abstract protected function readData($tag, &$request, &$response);

    /**
     * @param string $tag
     * @param array $request
     * @param array $response
     */
    abstract protected function writeData($tag, $request, $response);

    /**
     * @param string $tag
     */
    abstract protected function removeData($tag);

    /**
     * @return array
     */
    abstract protected function readHistory();

    /**
     * @param array $rows
     */
    abstract protected function writeHistory($rows);

    /**
     * @return array
     */
    abstract protected function readCollection();

    /**
     * @param array $rows
     */
    abstract protected function writeCollection($rows);
}