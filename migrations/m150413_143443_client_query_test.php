<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150413_143443_client_query_test extends Migration
{
    public function up()
    {
        $this->createTable('{{%client_query_test}}', [
            'id' => Schema::TYPE_BIGPK,
            'user_id' => Schema::TYPE_INTEGER,
            'site_id' => Schema::TYPE_BIGINT,
            'page_id' => Schema::TYPE_BIGINT,
            'at' => Schema::TYPE_INTEGER,
            'call_info' => Schema::TYPE_STRING . '(70)',
            'data' => Schema::TYPE_TEXT,
            'title' => Schema::TYPE_STRING . '(70)',
            'description' => Schema::TYPE_STRING . '(255)',
            'options_data' => Schema::TYPE_TEXT,
        ], $this->tableOptions);

        $this->addForeignKey('client_query_test_user_rel', '{{%client_query_test}}', 'user_id',
            '{{%user}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('client_query_test_site_rel', '{{%client_query_test}}', 'site_id',
            '{{%client_site}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('client_query_test_page_rel', '{{%client_query_test}}', 'page_id',
            '{{%client_page}}', 'id', 'SET NULL', 'SET NULL');
        $this->createIndex('client_query_test_uspa', '{{%client_query_test}}',
            ['user_id', 'site_id', 'page_id', 'at']);
        $this->createIndex('client_query_test_title', '{{%client_query_test}}',
            ['user_id', 'site_id', 'page_id', 'title'], true);
    }

    public function down()
    {
        $this->dropTable('{{%client_query_test}}');
    }
    
}
