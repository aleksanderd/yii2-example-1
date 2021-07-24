<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150802_215045_widget_hit extends Migration
{
    public function up()
    {
        $this->createTable('{{%client_visit}}', [
            'id' => Schema::TYPE_BIGPK,
            'previous_id' => Schema::TYPE_BIGINT,
            'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'site_id' => Schema::TYPE_BIGINT,
            'at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'ip' => Schema::TYPE_STRING . '(255) NOT NULL DEFAULT ""',
            'ref_url' => Schema::TYPE_STRING . '(255) NOT NULL DEFAULT ""',
            'user_agent' => Schema::TYPE_STRING . '(255) NOT NULL DEFAULT ""',
        ], $this->tableOptions);

        $this->addForeignKey('cvisit_last_rel', '{{%client_visit}}', 'previous_id',
            '{{%client_visit}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('cvisit_user_rel', '{{%client_visit}}', 'user_id',
            '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('cvisit_site_rel', '{{%client_visit}}', 'site_id',
            '{{%client_site}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('cvisit_at', '{{%client_visit}}', 'at');
        $this->createIndex('cvisit_ip', '{{%client_visit}}', 'ip');
        $this->createIndex('cvisit_ref_url', '{{%client_visit}}', 'ref_url');

        $this->createTable('{{%widget_hit}}', [
            'id' => Schema::TYPE_BIGPK,
            'visit_id' => Schema::TYPE_BIGINT . ' NOT NULL',
            'page_id' => Schema::TYPE_BIGINT,
            'at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'ip' => Schema::TYPE_STRING . '(255) NOT NULL DEFAULT ""',
            'ref_url' => Schema::TYPE_STRING . '(255) NOT NULL DEFAULT ""',
            'url' => Schema::TYPE_STRING . '(255) NOT NULL DEFAULT ""',
        ], $this->tableOptions);

        $this->addForeignKey('whit_visit_rel', '{{%widget_hit}}', 'visit_id',
            '{{%client_visit}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('whit_page_rel', '{{%widget_hit}}', 'page_id',
            '{{%client_page}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('whit_at', '{{%widget_hit}}', 'at');
        $this->createIndex('whit_ip', '{{%widget_hit}}', 'ip');
        $this->createIndex('whit_ref_url', '{{%widget_hit}}', 'ref_url');
        $this->createIndex('whit_url', '{{%widget_hit}}', 'url');

        $this->addColumn('{{%client_query}}', 'hit_id', Schema::TYPE_BIGINT);
        $this->addForeignKey('client_query_hit_rel', '{{%client_query}}', 'hit_id',
            '{{%widget_hit}}', 'id', 'SET NULL', 'SET NULL');
    }

    public function down()
    {
        $this->dropForeignKey('client_query_hit_rel', '{{%client_query}}');
        $this->dropColumn('{{%client_query}}', 'hit_id');
        $this->dropTable('{{%widget_hit}}');
        $this->dropTable('{{%client_visit}}');
    }
    
}
