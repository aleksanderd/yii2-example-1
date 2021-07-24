<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150410_135418_page_init extends Migration
{
    public function up()
    {
        $this->createTable('{{%client_page}}', [
            'id' => Schema::TYPE_BIGPK,
            'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'site_id' => Schema::TYPE_BIGINT . ' NOT NULL',
            'priority' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 100',
            'title' => Schema::TYPE_STRING . '(255) NOT NULL DEFAULT ""',
            'type' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
            'pattern' => Schema::TYPE_STRING . '(255) NOT NULL',
            'options_data' => Schema::TYPE_TEXT,
        ], $this->tableOptions);

        $this->addForeignKey('client_page_user_rel', '{{%client_page}}', 'user_id',
            '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('client_page_site_rel', '{{%client_page}}', 'site_id',
            '{{%client_site}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('client_page_user_site_type_title', '{{%client_page}}',
            ['user_id', 'site_id', 'type', 'title'], true);
        $this->createIndex('client_page_user_site_type_pattern', '{{%client_page}}',
            ['user_id', 'site_id', 'type', 'pattern'], true);
        $this->createIndex('client_page_user_site_priority', '{{%client_page}}',
            ['user_id', 'site_id', 'priority']);
    }

    public function down()
    {
        $this->dropTable('{{%client_page}}');
    }
    
}
