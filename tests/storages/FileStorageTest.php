<?php

namespace tests\storages;

use yii\helpers\FileHelper;
use zhuravljov\yii\rest\Module;
use zhuravljov\yii\rest\storages\FileStorage;

/**
 * Class FileStorageTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class FileStorageTest extends StorageTestCase
{
    protected function tearDown()
    {
        FileHelper::removeDirectory(\Yii::getAlias('@runtime'));
        parent::tearDown();
    }

    protected function getStorageInstance()
    {
        return new FileStorage(new Module('test-module'), []);
    }

    protected function resetData()
    {
        $path = \Yii::getAlias('@runtime/test-module');
        FileHelper::removeDirectory($path);
        FileHelper::createDirectory($path);
        $fixtures = static::getParam('fixtures');
        foreach ($fixtures['records'] as $tag => $row) {
            file_put_contents("{$path}/{$tag}.data", serialize($row));
        }
        file_put_contents("{$path}/history.data", serialize($fixtures['history']));
        file_put_contents("{$path}/collection.data", serialize($fixtures['collection']));
    }
}
