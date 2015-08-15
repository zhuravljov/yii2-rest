<?php

namespace zhuravljov\yii\rest\components;

use Yii;
use yii\base\Object;
use yii\web\NotFoundHttpException;
use zhuravljov\yii\rest\models\RequestForm;

/**
 * Class Storage
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
abstract class Storage extends Object
{
    private $_history;

    private $_collection;

    /**
     * @param string $tag
     * @return RequestForm
     * @throws NotFoundHttpException
     */
    public function find($tag)
    {
        if ($data = $this->readData($tag)) {
            $model = new RequestForm();
            $model->setAttributes($data['request']);
            $model->response = $data['response'];

            return $model;
        } else {
            throw new NotFoundHttpException('Request not found.');
        }
    }

    /**
     * @param RequestForm $model
     * @return string tag
     */
    public function save(RequestForm $model)
    {
        $tag = uniqid();
        $this->writeData($tag, [
            'request' => $model->getAttributes(null, ['baseUrl', 'response']),
            'response' => $model->response,
        ]);
        $this->addToHistory($tag, [
            'tag' => $tag,
            'method' => $model->method,
            'endpoint' => $model->endpoint,
            'status' => $model->response['status'],
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
     */
    public function addToHistory($tag, array $data)
    {
        $this->getHistory();
        $this->_history[$tag] = $data;
        $this->writeHistory($this->_history);
    }

    /**
     * @param string $tag
     */
    public function removeFromHistory($tag)
    {
        $this->getHistory();
        if (isset($this->_history[$tag])) {
            unset($this->_history[$tag]);
            $this->writeHistory($this->_history);
        }
        if (!$this->getCollection($tag)) {
            $this->removeData($tag);
        }
    }

    /**
     * @param string $tag
     * @throws NotFoundHttpException
     */
    public function addToCollection($tag)
    {
        if ($data = $this->getHistory($tag)) {
            $this->getCollection();
            $this->_collection[$tag] = $data;
            $this->writeCollection($this->_collection);
        } else {
            throw new NotFoundHttpException('Request not found.');
        }
    }

    /**
     * @param string $tag
     */
    public function removeFromCollection($tag)
    {
        $this->getCollection();
        if (isset($this->_collection[$tag])) {
            unset($this->_collection[$tag]);
            $this->writeCollection($this->_collection);
        }
        if (!$this->getHistory($tag)) {
            $this->removeData($tag);
        }
    }

    /**
     * @param string $pattern
     * @param string $default
     * @return array
     * @deprecated
     */
    public function getCollectionGroups($pattern = '([^/]+)', $default = 'common')
    {
        $groups = [];

        foreach ($this->getCollection() as $tag => $row) {
            if (preg_match("|$pattern|", $row['endpoint'], $m)) {
                $key = $m[1];
            } else {
                $key = $default;
            }
            $groups[$key][$tag] = $row;
        }
        ksort($groups);

        $order = array_flip(['get', 'post', 'put', 'delete']);
        foreach ($groups as &$rows) {
            uasort($rows, function ($row1, $row2) use ($order) {
                $ind1 = isset($order[$row1['method']]) ? $order[$row1['method']] : null;
                $ind2 = isset($order[$row2['method']]) ? $order[$row2['method']] : null;
                if ($ind1 < $ind2) {
                    return -1;
                } elseif ($ind1 > $ind2) {
                    return 1;
                } else {
                    return strcmp($row1['endpoint'], $row2['endpoint']);
                }
            });
        }
        unset($rows);

        return $groups;
    }

    /**
     * @param string $tag
     * @return array|null
     */
    abstract protected function readData($tag);

    /**
     * @param string $tag
     * @param array $data
     */
    abstract protected function writeData($tag, array $data);


    /**
     * @param string $tag
     */
    abstract protected function removeData($tag);

    /**
     * @return array
     */
    abstract protected function readHistory();

    /**
     * @param array $data
     */
    abstract protected function writeHistory(array $data);

    /**
     * @return array
     */
    abstract protected function readCollection();

    /**
     * @param array $data
     */
    abstract protected function writeCollection(array $data);
}