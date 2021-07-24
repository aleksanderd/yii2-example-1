<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150411_114556_rule_init extends Migration
{
    public function up()
    {
        $this->createTable('{{%client_rule}}', [
            'id' => Schema::TYPE_BIGPK,
            'user_id' => Schema::TYPE_INTEGER,
            'site_id' => Schema::TYPE_BIGINT,
            'page_id' => Schema::TYPE_BIGINT,
            'priority' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 100',
            'title' => Schema::TYPE_STRING . '(70) NOT NULL',
            'description' => Schema::TYPE_STRING . '(255)',
            'tm_week' => Schema::TYPE_SMALLINT . ' UNSIGNED',
            'tm_day' => Schema::TYPE_INTEGER . ' UNSIGNED',
            'condition_data' => Schema::TYPE_TEXT,
            'result_data' => Schema::TYPE_TEXT,
        ], $this->tableOptions);

        $this->addForeignKey('client_rule_user_rel', '{{%client_rule}}', 'user_id',
            '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('client_rule_site_rel', '{{%client_rule}}', 'site_id',
            '{{%client_site}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('client_rule_page_rel', '{{%client_rule}}', 'page_id',
            '{{%client_page}}', 'id', 'SET NULL', 'SET NULL');
        $this->createIndex('client_rule_user_site_page_title', '{{%client_rule}}',
            ['user_id', 'site_id', 'title'], true);
        $this->createIndex('client_rule_user_site_page_priority', '{{%client_rule}}',
            ['user_id', 'site_id', 'priority']);

        $this->createTable('{{%client_rule_line}}', [
            'rule_id' => Schema::TYPE_BIGINT,
            'line_id' => Schema::TYPE_BIGINT,
            'priority' => Schema::TYPE_INTEGER,
            'options_data' => Schema::TYPE_TEXT,
        ], $this->tableOptions);

        $this->addForeignKey('client_rule_line_rule_rel', '{{%client_rule_line}}', 'rule_id',
            '{{%client_rule}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('client_rule_line_line_rel', '{{%client_rule_line}}', 'line_id',
            '{{%client_line}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('client_rule_line_rule_priority', '{{%client_rule_line}}', ['rule_id', 'priority']);

    }

    public function down()
    {
        $this->dropTable('{{%client_rule_line}}');
        $this->dropTable('{{%client_rule}}');
    }
    
}
