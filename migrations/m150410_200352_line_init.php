<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150410_200352_line_init extends Migration
{
    public function up()
    {
        $this->createTable('{{%client_line}}', [
            'id' => Schema::TYPE_BIGPK,
            'user_id' => Schema::TYPE_INTEGER,
            'type_id' => Schema::TYPE_INTEGER . ' UNSIGNED DEFAULT 0',
            'title' => Schema::TYPE_STRING . '(70)',
            'info' => Schema::TYPE_STRING . '(255)',
            'description' => Schema::TYPE_STRING . '(255)',
        ], $this->tableOptions);

        $this->addForeignKey('client_line_user_rel', '{{%client_line}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('client_line_user_title', '{{%client_line}}', ['user_id', 'title'], true);
        $this->createIndex('client_line_type_info', '{{%client_line}}', ['type_id', 'info']);
    }

    public function down()
    {
        $this->dropTable('{{%client_line}}');
    }
    
}
