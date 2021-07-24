<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150606_131220_variable extends Migration
{
    public function up()
    {
        $this->createTable('{{%variable}}', [
            'id' => Schema::TYPE_BIGPK,
            'user_id' => Schema::TYPE_INTEGER,
            'type_id' => Schema::TYPE_SMALLINT . ' UNSIGNED NOT NULL DEFAULT 0',
            'name' => Schema::TYPE_STRING . '(255) NOT NULL',
            'options_data' => Schema::TYPE_TEXT,
        ], $this->tableOptions);

        $this->addForeignKey('var_user_rel', '{{%variable}}',
            'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('var_user_name', '{{%variable}}', ['user_id', 'name'], true);

        $this->createTable('{{%variable_value}}', [
            'variable_id' => Schema::TYPE_BIGINT . ' NOT NULL',
            'user_id' => Schema::TYPE_INTEGER,
            'site_id' => Schema::TYPE_BIGINT,
            'page_id' => Schema::TYPE_BIGINT,
            'value_data' => Schema::TYPE_BINARY,
        ], $this->tableOptions);

//        $this->addPrimaryKey('var_val_variable_user_site_page', '{{%variable_value}}',
//            ['variable_id', 'user_id', 'site_id', 'page_id']);

        $this->addForeignKey('var_val_var_rel', '{{%variable_value}}', 'variable_id',
            '{{%variable}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('var_val_user_rel', '{{%variable_value}}', 'user_id',
            '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('var_val_site_rel', '{{%variable_value}}', 'site_id',
            '{{%client_site}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('var_val_page_rel', '{{%variable_value}}', 'page_id',
            '{{%client_page}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('var_val_variable_user_site_page', '{{%variable_value}}',
            ['variable_id', 'user_id', 'site_id', 'page_id'], true);

    }

    public function down()
    {
        $this->dropTable('{{%variable_value}}');
        $this->dropTable('{{%variable}}');
    }

}
