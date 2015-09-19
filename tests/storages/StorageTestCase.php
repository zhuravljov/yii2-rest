<?php

namespace tests\storages;

use tests\TestCase;
use zhuravljov\yii\rest\models\RequestForm;
use zhuravljov\yii\rest\models\ResponseRecord;

/**
 * Class StorageTestCase
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
abstract class StorageTestCase extends TestCase
{
    abstract protected function resetData();

    /**
     * @return \zhuravljov\yii\rest\storages\Storage
     */
    abstract protected function getStorageInstance();


    protected function setUp()
    {
        $this->mockWebApplication();
        $this->resetData();
    }

    public function testExists()
    {
        $storage = $this->getStorageInstance();
        $this->assertEquals(true, !!$storage->exists('1111111111111'));
        $this->assertEquals(false, !!$storage->exists('0000000000000'));
    }

    public function testLoad()
    {
        $actualModel = new RequestForm();
        $actualRecord = new ResponseRecord();
        $actualResult = $this->getStorageInstance()->load('1111111111111', $actualModel, $actualRecord);

        $this->assertEquals(true, $actualResult);
        $expectedRequest = static::getParam('fixtures')['records']['1111111111111']['request'];
        $this->assertEquals($expectedRequest, $actualModel->getAttributes());
        $expectedResponse = static::getParam('fixtures')['records']['1111111111111']['response'];
        $this->assertEquals($expectedResponse, get_object_vars($actualRecord));
    }

    public function testGetHistory()
    {
        $storage = $this->getStorageInstance();

        $this->assertEquals(
            static::getParam('fixtures')['history'],
            $storage->getHistory()
        );
        $this->assertEquals(
            static::getParam('fixtures')['history']['1111111111111'],
            $storage->getHistory('1111111111111')
        );
    }

    public function testGetCollection()
    {
        $storage = $this->getStorageInstance();

        $this->assertEquals(
            static::getParam('fixtures')['collection'],
            $storage->getCollection()
        );
        $this->assertEquals(
            static::getParam('fixtures')['collection']['1111111111111'],
            $storage->getCollection('1111111111111')
        );
    }

    public function testSave()
    {
        $storage = $this->getStorageInstance();


        $expectedModel = new RequestForm([
            'method' => 'get',
            'endpoint' => 'books',
            'tab' => '1',
            'queryKeys' => [],
            'queryValues' => [],
            'queryActives' => [],
            'bodyKeys' => [],
            'bodyValues' => [],
            'bodyActives' => [],
            'headerKeys' => [],
            'headerValues' => [],
            'headerActives' => [],
            'description' => 'List books.',
        ]);
        $expectedRecord = new ResponseRecord();
        $expectedRecord->status = '200';
        $expectedRecord->duration = '0.123';
        $expectedRecord->headers = [
            'Http-Code' => ['200'],
            'Content-Type' => ['application/json; charset=UTF-8'],
        ];
        $expectedRecord->content = '[]';
        $actualTag = $storage->save($expectedModel, $expectedRecord);
        $actualModel = new RequestForm();
        $actualRecord = new ResponseRecord();
        $storage->load($actualTag, $actualModel, $actualRecord);

        $this->assertEquals($expectedModel->getAttributes(), $actualModel->getAttributes());
        $this->assertEquals(get_object_vars($expectedRecord), get_object_vars($actualRecord));


        $expectedHistoryRow = [
            'method' => $expectedModel->method,
            'endpoint' => $expectedModel->endpoint,
            'description' => $expectedModel->description,
            'status' => $expectedRecord->status,
            'time' => (string)time(),
        ];
        $actualHistoryRow = $storage->getHistory($actualTag);

        $this->assertEquals($expectedHistoryRow, $actualHistoryRow);


        $storage->addToCollection($actualTag);
        $expectedCollectionRow = $expectedHistoryRow;
        $expectedCollectionRow['time'] = (string)time();
        $actualCollectionRow = $storage->getCollection($actualTag);

        $this->assertEquals($expectedCollectionRow, $actualCollectionRow);
    }

    public function testRemoveFromHistory()
    {
        $storage = $this->getStorageInstance();
        $storage->removeFromHistory('1111111111111');

        $this->assertEquals(null, $storage->getHistory('1111111111111'));
    }

    public function testRemoveFromCollection()
    {
        $storage = $this->getStorageInstance();
        $storage->removeFromCollection('1111111111111');

        $this->assertEquals(null, $storage->getCollection('1111111111111'));
    }

    public function testRemove()
    {
        $storage = $this->getStorageInstance();
        $storage->removeFromHistory('1111111111111');
        $storage->removeFromCollection('1111111111111');
        $actualResult = $storage->load('1111111111111', new RequestForm(), new ResponseRecord());

        $this->assertEquals(false, $actualResult);
    }
}