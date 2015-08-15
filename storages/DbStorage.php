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
     * CREATE TABLE rest (
     *     tag VARCHAR(24) NOT NULL,
     *     module_id VARCHAR(64) NOT NULL,
     *     data LONGBLOB NOT NULL,
     *     method VARCHAR(8),
     *     endpoint VARCHAR(128),
     *     status VARCHAR(3),
     *     stored_at INT(11) DEFAULT NULL,
     *     favorited_at INT(11) DEFAULT NULL,
     *     PRIMARY KEY (tag),
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
    protected function readData($tag)
    {
        $raw = (new Query())
            ->select('data')
            ->from($this->tableName)
            ->andWhere(['tag' => $tag])
            ->andWhere(['module_id' => $this->module->id])
            ->createCommand($this->db)
            ->queryScalar();

        if ($raw !== null) {
            return unserialize($raw);
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    protected function writeData($tag, array $data)
    {
        $this->db->createCommand()
            ->insert($this->tableName, [
                'tag' => $tag,
                'module_id' => $this->module->id,
                'data' => serialize($data),
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
        return (new Query())
            ->select(['tag', 'method', 'endpoint', 'status', 'stored_at'])
            ->from($this->tableName)
            ->andWhere(['module_id' => $this->module->id])
            ->andWhere('stored_at IS NOT NULL')
            ->orderBy(['tag' => SORT_ASC])
            ->indexBy('tag')
            ->all($this->db);
    }

    /**
     * @inheritdoc
     */
    protected function writeHistory(array $rows)
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
        return (new Query())
            ->select(['tag', 'method', 'endpoint', 'status', 'stored_at'])
            ->from($this->tableName)
            ->andWhere(['module_id' => $this->module->id])
            ->andWhere('favorited_at IS NOT NULL')
            ->orderBy(['tag' => SORT_ASC])
            ->indexBy('tag')
            ->all($this->db);
    }

    /**
     * @inheritdoc
     */
    protected function writeCollection(array $rows)
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
                $this->db->createCommand()
                    ->update($this->tableName, ['favorited_at' => time()], [
                        'tag' => $tag,
                        'module_id' => $this->module->id,
                    ])
                    ->execute();
            }
        });
    }
}