<?php

namespace tests\storages;

use yii\db\Connection;
use zhuravljov\yii\rest\Module;
use zhuravljov\yii\rest\storages\DbStorage;

/**
 * Class DbStorageTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class DbStorageTest extends StorageTestCase
{
    private $_connection;

    protected function tearDown()
    {
        $this->getConnection()->pdo->exec('DROP TABLE IF EXISTS rest');
        parent::tearDown();
    }

    protected function getStorageInstance()
    {
        return new DbStorage(new Module('test-module'), [
            'db' => $this->getConnection(),
        ]);
    }

    /**
     * @return Connection
     */
    protected function getConnection()
    {
        if ($this->_connection === null) {
            $databases = static::getParam('databases');
            $params = $databases['mysql'];
            $db = new Connection();
            $db->dsn = $params['dsn'];
            $db->username = $params['username'];
            $db->password = $params['password'];
            $this->_connection = $db;
        }

        return $this->_connection;
    }

    protected function resetData()
    {
        $db = $this->getConnection();
        $db->open();

        // Schema

        $db->pdo->exec('
            CREATE TABLE IF NOT EXISTS rest (
                id INT(11) NOT NULL AUTO_INCREMENT,
                tag VARCHAR(24) NOT NULL,
                module_id VARCHAR(64) NOT NULL,
                request LONGBLOB NOT NULL,
                response LONGBLOB NOT NULL,
                method VARCHAR(8),
                endpoint VARCHAR(128),
                description LONGTEXT,
                status VARCHAR(3),
                stored_at INT(11) DEFAULT NULL,
                favorited_at INT(11) DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY tag (tag, module_id),
                KEY module_id (module_id)
            );
        ');

        // Data

        $db->createCommand()->delete('rest', [
            'module_id' => 'test-module',
        ])->execute();

        $fixtures = static::getParam('fixtures');
        foreach ($fixtures['records'] as $tag => $row) {
            $db->createCommand()->insert('rest', [
                'tag' => $tag,
                'module_id' => 'test-module',
                'request' => serialize($row['request']),
                'response' => serialize($row['response']),
            ])->execute();
        }
        foreach ($fixtures['history'] as $tag => $row) {
            $row['stored_at'] = $row['time'];
            unset($row['time']);
            $db->createCommand()->update('rest', $row , [
                'tag' => $tag,
                'module_id' => 'test-module',
            ])->execute();
        }
        foreach ($fixtures['collection'] as $tag => $row) {
            $row['favorited_at'] = $row['time'];
            unset($row['time']);
            $db->createCommand()->update('rest', $row , [
                'tag' => $tag,
                'module_id' => 'test-module',
            ])->execute();
        }
    }
}