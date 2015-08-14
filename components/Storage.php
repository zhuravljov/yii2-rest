<?php

namespace zhuravljov\yii\rest\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\helpers\FileHelper;
use zhuravljov\yii\rest\models\RequestForm;

/**
 * Class Storage
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class Storage extends Component
{
    /**
     * @var \zhuravljov\yii\rest\Module
     */
    public $module;

    private $_logPath;
    private $_history;
    private $_collection;

    public function init()
    {
        $this->_logPath = Yii::getAlias($this->module->logPath)
            . '/' . $this->module->id;
    }

    /**
     * @param string $tag
     * @return null|RequestForm
     */
    public function find($tag)
    {
        $fileName = "/{$this->_logPath}/{$tag}.data";
        if (file_exists($fileName)) {
            $model = new RequestForm([
                'baseUrl' => $this->module->baseUrl,
            ]);

            $data = unserialize(file_get_contents($fileName));
            $model->setAttributes($data['request']);
            $model->response = $data['response'];

            return $model;
        } else {
            return null;
        }
    }

    /**
     * @param RequestForm $model
     * @return string tag
     */
    public function save(RequestForm $model)
    {
        $tag = uniqid();

        $this->addToHistory($tag, [
            'tag' => $tag,
            'method' => $model->method,
            'endpoint' => $model->endpoint,
            'status' => $model->response['status'],
        ]);

        file_put_contents(
            "/{$this->_logPath}/{$tag}.data",
            serialize([
                'request' => $model->getAttributes(null, [
                    'baseUrl',
                    'response'
                ]),
                'response' => $model->response,
            ])
        );

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
            $fileName = "/{$this->_logPath}/history.data";
            if (file_exists($fileName)) {
                $this->_history = unserialize(file_get_contents($fileName));
            } else {
                $this->_history = [];
            }
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
            $fileName = "/{$this->_logPath}/collection.data";
            if (file_exists($fileName)) {
                $this->_collection = unserialize(file_get_contents($fileName));
            } else {
                $this->_collection = [];
            }
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
    public function addToHistory($tag, $data)
    {
        $this->getHistory();
        $this->_history[$tag] = $data;
        FileHelper::createDirectory($this->_logPath);
        file_put_contents(
            "/{$this->_logPath}/history.data",
            serialize($this->_history)
        );
    }

    /**
     * @param string $tag
     */
    public function removeFromHistory($tag)
    {
        $this->getHistory();
        if (isset($this->_history[$tag])) {
            unset($this->_history[$tag]);
            file_put_contents(
                "/{$this->_logPath}/history.data",
                serialize($this->_history)
            );
        }
        $fileName = "/{$this->_logPath}/{$tag}.data";
        if (file_exists($fileName) && !$this->getCollection($tag)) {
            unlink($fileName);
        }
    }

    public function addToCollection($tag)
    {
        if ($data = $this->getHistory($tag)) {
            $this->getCollection();
            $this->_collection[$tag] = $data;
            FileHelper::createDirectory($this->_logPath);
            file_put_contents(
                "/{$this->_logPath}/collection.data",
                serialize($this->_collection)
            );
        } else {
            throw new InvalidParamException('Tag not found.');
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
            file_put_contents(
                "/{$this->_logPath}/collection.data",
                serialize($this->_collection)
            );
        }
        $fileName = "/{$this->_logPath}/{$tag}.data";
        if (file_exists($fileName) && !$this->getHistory($tag)) {
            unlink($fileName);
        }
    }

    /**
     * @param string $pattern
     * @param string $default
     * @return array
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
}