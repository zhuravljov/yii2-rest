<?php

namespace zhuravljov\yii\rest\storages;

use Yii;
use yii\helpers\FileHelper;

/**
 * Class FileStorage
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class FileStorage extends Storage
{
    /**
     * @var string log path
     */
    public $path = '@runtime';

    public function init()
    {
        parent::init();
        $this->path = Yii::getAlias($this->path . '/' .$this->module->id);
    }

    /**
     * @inheritdoc
     */
    public function exists($tag)
    {
        return file_exists("{$this->path}/{$tag}.data");
    }

    /**
     * @inheritdoc
     */
    protected function readData($tag, &$request, &$response)
    {
        $fileName = "{$this->path}/{$tag}.data";
        if (file_exists($fileName)) {
            $data = unserialize(file_get_contents($fileName));
            $request = $data['request'];
            $response = $data['response'];
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    protected function writeData($tag, $request, $response)
    {
        FileHelper::createDirectory($this->path);
        file_put_contents("{$this->path}/{$tag}.data", serialize([
            'request' => $request,
            'response' => $response,
        ]));
    }

    /**
     * @inheritdoc
     */
    protected function removeData($tag)
    {
        $fileName = "{$this->path}/{$tag}.data";
        if (file_exists($fileName)) {
            unlink($fileName);
        }
    }

    /**
     * @inheritdoc
     */
    protected function readHistory()
    {
        $fileName = "{$this->path}/history.data";
        if (file_exists($fileName)) {
            return unserialize(file_get_contents($fileName));
        } else {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    protected function writeHistory($rows)
    {
        FileHelper::createDirectory($this->path);
        file_put_contents("{$this->path}/history.data", serialize($rows));
    }

    /**
     * @inheritdoc
     */
    protected function readCollection()
    {
        $fileName = "{$this->path}/collection.data";
        if (file_exists($fileName)) {
            return unserialize(file_get_contents($fileName));
        } else {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    protected function writeCollection($rows)
    {
        FileHelper::createDirectory($this->path);
        file_put_contents("{$this->path}/collection.data", serialize($rows));
    }
}