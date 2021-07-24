<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150523_171806_notification extends Migration
{
    public function up()
    {
        $this->createTable('{{%notification}}', [
            'id' => Schema::TYPE_BIGPK,
            'user_id' => Schema::TYPE_INTEGER,
            'site_id' => Schema::TYPE_BIGINT,
            'page_id' => Schema::TYPE_BIGINT,
            'query_id' => Schema::TYPE_BIGINT,
            'type' => Schema::TYPE_SMALLINT,
            'at' => Schema::TYPE_INTEGER,
            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
            'from' => Schema::TYPE_STRING . '(255)',
            'to' => Schema::TYPE_STRING . '(255)',
            'subject' => Schema::TYPE_STRING . '(255)',
            'body' => Schema::TYPE_TEXT,
            'description' => Schema::TYPE_STRING . '(255)',

        ], $this->tableOptions);

        $this->addForeignKey('notification_user_rel', '{{%notification}}', 'user_id',
            '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('notification_site_rel', '{{%notification}}', 'site_id',
            '{{%client_site}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('notification_page_rel', '{{%notification}}', 'page_id',
            '{{%client_page}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('notification_query_rel', '{{%notification}}', 'query_id',
            '{{%client_query}}', 'id', 'SET NULL', 'SET NULL');
        $this->createIndex('notification_ids', '{{%notification}}',
            ['user_id', 'site_id', 'page_id', 'query_id']);
        $this->createIndex('notification_at', '{{%notification}}', ['at']);
        $this->createIndex('notification_status', '{{%notification}}', ['status']);
    }

    public function down()
    {
        $this->dropTable('{{%notification}}');
    }
    
}
