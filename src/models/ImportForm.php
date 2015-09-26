<?php

namespace zhuravljov\yii\rest\models;

use yii\base\InvalidParamException;
use yii\base\Model;
use yii\helpers\Json;
use yii\web\UploadedFile;
use zhuravljov\yii\rest\storages\Storage;

/**
 * Class ImportForm
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class ImportForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $dataFile;
    /**
     * @var array
     */
    public $data;

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        if (parent::load($data, $formName)) {
            $this->dataFile = UploadedFile::getInstance($this, 'dataFile');
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['dataFile', 'required'],
            ['dataFile', 'file'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'dataFile' => 'Data File',
        ];
    }

    /**
     * @param Storage $storage
     * @return integer|false
     */
    public function save(Storage $storage)
    {
        if (!$this->validate()) {
            return false;
        }

        $content = file_get_contents($this->dataFile->tempName);
        try {
            $data = Json::decode($content);
        } catch (InvalidParamException $e) {
            $this->addError('dataFile', 'Json parser: ' . $e->getMessage());
            return false;
        }
        try {
            $count = $storage->importCollection($data);
        } catch (InvalidParamException $e) {
            $this->addError('dataFile', 'Import: ' . $e->getMessage());
            return false;
        }

        return $count;
    }
}