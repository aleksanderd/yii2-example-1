<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150414_122823_client_query extends Migration
{
    public function up()
    {
        $this->createTable('{{%client_query}}', [
            'id' => Schema::TYPE_BIGPK,
            'test_id' => Schema::TYPE_BIGINT . ' DEFAULT NULL',
            'status' => Schema::TYPE_SMALLINT . ' DEFAULT 0',
            'user_id' => Schema::TYPE_INTEGER,
            'site_id' => Schema::TYPE_BIGINT,
            'page_id' => Schema::TYPE_BIGINT,
            'rule_id' => Schema::TYPE_BIGINT,
            'at' => Schema::TYPE_INTEGER,
            'time' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'url' => Schema::TYPE_STRING . '(255) NOT NULL DEFAULT ""',
            'call_info' => Schema::TYPE_STRING . '(70)',
            'record_time' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'record_data' => Schema::TYPE_TEXT,
            'result_data' => Schema::TYPE_TEXT,
            'data' => Schema::TYPE_TEXT,
        ], $this->tableOptions);

        $this->addForeignKey('client_query_qtest_rel', '{{%client_query}}', 'test_id',
            '{{%client_query_test}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('client_query_user_rel', '{{%client_query}}', 'user_id',
            '{{%user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('client_query_site_rel', '{{%client_query}}', 'site_id',
            '{{%client_site}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('client_query_page_rel', '{{%client_query}}', 'page_id',
            '{{%client_page}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('client_query_rule_rel', '{{%client_query}}', 'rule_id',
            '{{%client_rule}}', 'id', 'SET NULL', 'SET NULL');
        $this->createIndex('client_query_suspa', '{{%client_query}}',
            ['status', 'user_id', 'site_id', 'page_id', 'at']);
        $this->createIndex('client_query_time', '{{%client_query}}', ['time', 'record_time']);
        $this->createIndex('client_query_url', '{{%client_query}}', ['url']);
    }

    public function down()
    {
        $this->dropTable('{{%client_query}}');
    }

}
