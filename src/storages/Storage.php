<?php

namespace zhuravljov\yii\rest\storages;

use Yii;
use yii\base\InvalidParamException;
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
        }
        if ($tag === null) {
            return $this->_collection;
        } elseif (isset($this->_collection[$tag])) {
            return $this->_collection[$tag];
        } else {
            return $default;
        }
    }

    /**
     * @param string $tag
     * @param array $data
     * @return boolean
     */
    protected function addToHistory($tag, array $data)
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
     * @return integer count of removed records
     */
    public function clearHistory()
    {
        $this->getHistory();
        foreach (array_keys($this->_history) as $tag) {
            if (!$this->getCollection($tag)) {
                $this->removeData($tag);
            }
        }
        $count = count($this->_history);
        $this->writeHistory($this->_history = []);

        return $count;
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
     * @return array
     */
    public function exportCollection()
    {
        $items = [];
        foreach (array_keys($this->getCollection()) as $tag) {
            $this->readData($tag, $items[$tag]['request'], $items[$tag]['response']);
        }

        return $items;
    }

    /**
     * @param array $data
     * @return integer number of records that were imported
     */
    public function importCollection($data)
    {
        if (!is_array($data)) {
            throw new InvalidParamException('Data must be an array.');
        }

        // Validate

        /** @var RequestForm[] $requests */
        $requests = [];
        /** @var ResponseRecord[] $responses */
        $responses = [];
        foreach ($data as $tag => $row) {
            if (!preg_match('/^[a-f0-9]+$/', $tag)) {
                throw new InvalidParamException("Tag {$tag} must be a string and contains a-f0-9 symbols only.");
            }

            if (!isset($row['request'], $row['response'])) {
                throw new InvalidParamException("Row {$tag} must contains request and response.");
            }

            $request = new RequestForm();
            $request->setAttributes($row['request']);
            if (!$request->validate()) {
                $errors = $request->getFirstErrors();
                throw new InvalidParamException(reset($errors));
            }
            $requests[$tag] = $request;

            $response = new ResponseRecord();
            try {
                $response->status = $row['response']['status'];
                $response->duration = $row['response']['duration'];
                $response->headers = $row['response']['headers'];
                $response->content = $row['response']['content'];
            } catch (\Exception $e) {
                throw new InvalidParamException($e->getMessage(), $e->getCode(), $e);
            }
            $responses[$tag] = $response;
        }

        // Save

        $count = 0;
        $this->_collection = $this->readCollection();
        foreach ($requests as $tag => $request) {
            if (!$this->exists($tag)) {
                $this->writeData($tag, $request->getAttributes(), get_object_vars($responses[$tag]));
                $this->_collection[$tag] = [
                    'method' => $request->method,
                    'endpoint' => $request->endpoint,
                    'description' => $request->description,
                    'status' => $responses[$tag]->status,
                    'time' => time(),
                ];
                $count++;
            }
        }
        $this->writeCollection($this->_collection);

        return $count;
    }

    /**
     * @param string $tag
     * @return boolean
     */
    abstract public function exists($tag);

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