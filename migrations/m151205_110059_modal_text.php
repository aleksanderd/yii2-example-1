<?php

use dektrium\user\migrations\Migration;

class m151205_110059_modal_text extends Migration
{
    public function up()
    {
        $this->createTable('{{%modal_text}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'language' => $this->string(),
            'title' => $this->string()->notNull(),

            'm_title' => $this->string()->notNull()->defaultValue(''),
            'm_submit' => $this->string()->notNull()->defaultValue(''),
            'm_description' => $this->text()->notNull()->defaultValue(''),
        ], $this->tableOptions);

        $this->addForeignKey('modal_text_user_rel', '{{%modal_text}}', 'user_id', '{{%user}}', 'id');
        $this->createIndex('modal_text_user_title', '{{%modal_text}}', ['user_id', 'title'], true);

        $this->createTable('{{%modal_text_stat}}', [
            'text_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'site_id' => $this->bigInteger()->notNull(),

            'datetime' => $this->integer()->notNull(),
            'period' => $this->integer()->notNull()->defaultValue(86400),

            'trigger' => $this->smallInteger()->notNull()->defaultValue(0),
            'wins' => $this->integer()->notNull()->defaultValue(0),
            'wins_uni' => $this->integer()->notNull()->defaultValue(0),
            'queries' => $this->integer()->notNull()->defaultValue(0),

        ], $this->tableOptions);

        $this->addForeignKey('modal_text_stat_user_rel', '{{%modal_text_stat}}', 'user_id', '{{%user}}', 'id');
        $this->addForeignKey('modal_text_stat_site_rel', '{{%modal_text_stat}}', 'site_id', '{{%client_site}}', 'id');
        $this->addForeignKey('modal_text_stat_text_rel', '{{%modal_text_stat}}', 'text_id', '{{%modal_text}}', 'id');
        $this->addPrimaryKey('modal_text_stat_pk', '{{%modal_text_stat}}',
            ['text_id', 'user_id', 'site_id', 'trigger', 'period', 'datetime']);
    }

    public function down()
    {
        $this->dropTable('{{%modal_text_stat}}');
        $this->dropTable('{{%modal_text}}');
    }

}
