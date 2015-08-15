<?php

namespace zhuravljov\yii\rest\components;

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

    /**
     * @param \zhuravljov\yii\rest\Module $module
     * @param array $config
     */
    public function __construct($module, $config = [])
    {
        parent::__construct($config);
        $this->path = Yii::getAlias($this->path . '/' .$module->id);
    }

    /**
     * @inheritdoc
     */
    protected function readData($tag)
    {
        $fileName = "/{$this->path}/{$tag}.data";
        if (file_exists($fileName)) {
            return unserialize(file_get_contents($fileName));
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    protected function writeData($tag, array $data)
    {
        FileHelper::createDirectory($this->path);
        file_put_contents("/{$this->path}/{$tag}.data", serialize($data));
    }

    /**
     * @inheritdoc
     */
    protected function removeData($tag)
    {
        $fileName = "/{$this->path}/{$tag}.data";
        if (file_exists($fileName)) {
            unlink($fileName);
        }
    }

    /**
     * @inheritdoc
     */
    protected function readHistory()
    {
        $fileName = "/{$this->path}/history.data";
        if (file_exists($fileName)) {
            return unserialize(file_get_contents($fileName));
        } else {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    protected function writeHistory(array $data)
    {
        FileHelper::createDirectory($this->path);
        file_put_contents("/{$this->path}/history.data", serialize($data));
    }

    /**
     * @inheritdoc
     */
    protected function readCollection()
    {
        $fileName = "/{$this->path}/collection.data";
        if (file_exists($fileName)) {
            return unserialize(file_get_contents($fileName));
        } else {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    protected function writeCollection(array $data)
    {
        FileHelper::createDirectory($this->path);
        file_put_contents("/{$this->path}/collection.data", serialize($data));
    }
}