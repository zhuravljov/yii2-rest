<?php

namespace zhuravljov\yii\rest\storages;

use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;

/**
 * Class DbStorage
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class DbStorage extends Storage
{
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     */
    public $db = 'db';
    /**
     * @var string the name of the DB table that stores the data.
     * The table should be pre-created as follows:
     *
     * ~~~
     * CREATE TABLE IF NOT EXISTS rest (
     *     id INT(11) NOT NULL AUTO_INCREMENT,
     *     tag VARCHAR(24) NOT NULL,
     *     module_id VARCHAR(64) NOT NULL,
     *     request LONGBLOB NOT NULL,
     *     response LONGBLOB NOT NULL,
     *     method VARCHAR(8),
     *     endpoint VARCHAR(128),
     *     description LONGTEXT,
     *     status VARCHAR(3),
     *     stored_at INT(11) DEFAULT NULL,
     *     favorited_at INT(11) DEFAULT NULL,
     *     PRIMARY KEY (id),
     *     UNIQUE KEY tag (tag, module_id),
     *     KEY module_id (module_id)
     * );
     * ~~~
     */
    public $tableName = '{{%rest}}';

    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    /**
     * @inheritdoc
     */
    public function exists($tag)
    {
        return (new Query())
            ->from($this->tableName)
            ->andWhere(['tag' => $tag])
            ->andWhere(['module_id' => $this->module->id])
            ->exists($this->db);
    }

    /**
     * @inheritdoc
     */
    protected function readData($tag, &$request, &$response)
    {
        $query = (new Query())
            ->select(['request', 'response'])
            ->from($this->tableName)
            ->andWhere(['tag' => $tag])
            ->andWhere(['module_id' => $this->module->id]);

        if ($row = $query->one($this->db)) {
            $request = unserialize($row['request']);
            $response = unserialize($row['response']);
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
        $this->db->createCommand()
            ->insert($this->tableName, [
                'tag' => $tag,
                'module_id' => $this->module->id,
                'request' => serialize($request),
                'response' => serialize($response),
            ])
            ->execute();
    }

    /**
     * @inheritdoc
     */
    protected function removeData($tag)
    {
        $this->db->createCommand()
            ->delete($this->tableName, [
                'tag' => $tag,
                'module_id' => $this->module->id,
            ])
            ->execute();
    }

    /**
     * @inheritdoc
     */
    protected function readHistory()
    {
        $query = (new Query())
            ->select(['tag', 'method', 'endpoint', 'description', 'status', 'time' => 'stored_at'])
            ->from($this->tableName)
            ->andWhere(['module_id' => $this->module->id])
            ->andWhere('stored_at IS NOT NULL')
            ->orderBy(['tag' => SORT_ASC])
            ->indexBy('tag');

        $rows = $query->all($this->db);
        foreach ($rows as &$row) {
            unset($row['tag']);
        }
        unset($row);

        return $rows;
    }

    /**
     * @inheritdoc
     */
    protected function writeHistory($rows)
    {
        $this->db->transaction(function () use ($rows) {
            $old = $this->readHistory();
            foreach (array_diff_key($old, $rows) as $tag => $row) {
                $this->db->createCommand()
                    ->update($this->tableName, ['stored_at' => null], [
                        'tag' => $tag,
                        'module_id' => $this->module->id,
                    ])
                    ->execute();
            }
            foreach (array_diff_key($rows, $old) as $tag => $row) {
                $row['stored_at'] = $row['time'];
                unset($row['time']);
                $this->db->createCommand()
                    ->update($this->tableName, $row, [
                        'tag' => $tag,
                        'module_id' => $this->module->id,
                    ])
                    ->execute();
            }
        });
    }

    /**
     * @inheritdoc
     */
    protected function readCollection()
    {
        $query = (new Query())
            ->select(['tag', 'method', 'endpoint', 'description', 'status', 'time' => 'favorited_at'])
            ->from($this->tableName)
            ->andWhere(['module_id' => $this->module->id])
            ->andWhere('favorited_at IS NOT NULL')
            ->orderBy(['tag' => SORT_ASC])
            ->indexBy('tag');

        $rows = $query->all($this->db);
        foreach ($rows as &$row) {
            unset($row['tag']);
        }
        unset($row);

        return $rows;
    }

    /**
     * @inheritdoc
     */
    protected function writeCollection($rows)
    {
        $this->db->transaction(function () use ($rows) {
            $old = $this->readCollection();
            foreach (array_diff_key($old, $rows) as $tag => $row) {
                $this->db->createCommand()
                    ->update($this->tableName, ['favorited_at' => null], [
                        'tag' => $tag,
                        'module_id' => $this->module->id,
                    ])
                    ->execute();
            }
            foreach (array_diff_key($rows, $old) as $tag => $row) {
                $row['favorited_at'] = $row['time'];
                unset($row['time']);
                $this->db->createCommand()
                    ->update($this->tableName, $row, [
                        'tag' => $tag,
                        'module_id' => $this->module->id,
                    ])
                    ->execute();
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function clearHistory()
    {
        return $this->db->transaction(function () {
            return parent::clearHistory();
        });
    }

    /**
     * @inheritdoc
     */
    public function importCollection($data)
    {
        return $this->db->transaction(function () use ($data) {
            return parent::importCollection($data);
        });
    }
}