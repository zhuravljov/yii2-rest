<?php

namespace zhuravljov\yii\rest\migrations;

use yii\db\Migration;

class m000000_000001_storage extends Migration
{
    public $tableName = '{{%rest}}';
    public $tableOptions = null;

    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'tag' => $this->string(24)->notNull(),
            'module_id' => $this->string(64)->notNull(),
            'request' => 'LONGBLOB NOT NULL',
            'response' => 'LONGBLOB NOT NULL',
            'method' => $this->string(8),
            'endpoint' => $this->string(128),
            'description' => $this->text(),
            'status' => $this->string(3),
            'stored_at' => $this->integer(),
            'favorited_at' => $this->integer(),
        ], $this->tableOptions);
        $this->createIndex('tag', $this->tableName, ['tag', 'module_id'], true);
        $this->createIndex('module_id', $this->tableName, 'module_id');
    }

    public function down()
    {
        $this->dropTable($this->tableName);
    }
}