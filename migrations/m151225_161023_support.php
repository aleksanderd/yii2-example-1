<?php

use dektrium\user\migrations\Migration;

class m151225_161023_support extends Migration
{
    public function up()
    {
        $this->createTable('{{%s_ticket}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'site_id' => $this->bigInteger(),
            'created_at' => $this->integer()->notNull()->defaultValue(0),
            'updated_at' => $this->integer()->notNull()->defaultValue(0),
            'topic_id' => $this->integer()->notNull()->defaultValue(0),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'title' => $this->string()->notNull(),
        ], $this->tableOptions);
        $this->addForeignKey('s_ticket_user_rel', '{{%s_ticket}}', 'user_id', '{{%user}}', 'id');
        $this->addForeignKey('s_ticket_site_rel', '{{%s_ticket}}', 'site_id', '{{%client_site}}', 'id');
        $this->createIndex('s_ticket_ts', '{{%s_ticket}}', ['created_at', 'updated_at']);

        $this->createTable('{{%s_message}}', [
            'id' => $this->primaryKey(),
            'ticket_id' => $this->integer()->notNull(),
            'parent_id' => $this->integer(),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull()->defaultValue(0),
            'updated_at' => $this->integer()->notNull()->defaultValue(0),
            'message' => $this->text()->notNull(),
        ], $this->tableOptions);
        $this->addForeignKey('s_message_ticket_rel', '{{%s_message}}', 'ticket_id', '{{%s_ticket}}', 'id');
        $this->addForeignKey('s_message_parent_rel', '{{%s_message}}', 'parent_id', '{{%s_message}}', 'id');
        $this->addForeignKey('s_message_user_rel', '{{%s_message}}', 'user_id', '{{%user}}', 'id');
        $this->createIndex('s_message_ts', '{{%s_message}}', ['created_at', 'updated_at']);
    }

    public function down()
    {
        $this->dropTable('{{%s_message}}');
        $this->dropTable('{{%s_ticket}}');
    }

}
