<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150410_135417_site_init extends Migration
{
    public function up()
    {
        $this->createTable('{{%client_site}}', [
            'id' => Schema::TYPE_BIGPK,
            'user_id' => Schema::TYPE_INTEGER,
            'title' => Schema::TYPE_STRING . '(70)',
            'description' => Schema::TYPE_STRING . '(255)',
            'url' => Schema::TYPE_STRING . '(255)',
            'allowed_cc_data' => Schema::TYPE_TEXT,
            'default_cc' => Schema::TYPE_STRING . '(11)',
        ], $this->tableOptions);

        $this->addForeignKey('client_site_user_rel', '{{%client_site}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('client_site_user_title', '{{%client_site}}', ['user_id', 'title'], true);
        $this->createIndex('client_site_user_url', '{{%client_site}}', ['user_id', 'url'], true);
    }

    public function down()
    {
        $this->dropTable('{{%client_site}}');
    }
    
}
