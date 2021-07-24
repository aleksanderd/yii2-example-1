<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150626_122823_client_query_call extends Migration
{
    public function up()
    {
        $this->createTable('{{%client_query_call}}', [
            'id' => Schema::TYPE_BIGPK,
            'query_id' => Schema::TYPE_BIGINT . ' DEFAULT NULL',
            'line_id' => Schema::TYPE_BIGINT . ' DEFAULT NULL',
            'info' => Schema::TYPE_STRING . '(255)',
            'started_at' => Schema::TYPE_INTEGER . ' UNSIGNED',
            'failed_at' => Schema::TYPE_INTEGER . ' UNSIGNED',
            'connected_at' => Schema::TYPE_INTEGER . ' UNSIGNED',
            'disconnected_at' => Schema::TYPE_INTEGER . ' UNSIGNED',
            'duration' => Schema::TYPE_INTEGER  . ' UNSIGNED',
            'cost' => Schema::TYPE_DECIMAL . '(10,4) UNSIGNED',
            'direction' => Schema::TYPE_STRING . '(255)',
        ], $this->tableOptions);

        $this->addForeignKey('cq_call_client_query_rel', '{{%client_query_call}}', 'query_id',
            '{{%client_query}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('cq_call_client_line_rel', '{{%client_query_call}}', 'line_id',
            '{{%client_line}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('cq_call_at', '{{%client_query_call}}',
            ['started_at', 'failed_at', 'connected_at', 'disconnected_at']);
        $this->createIndex('cq_call_duration_cost', '{{%client_query_call}}', ['duration', 'cost']);
        $this->createIndex('cq_call_info', '{{%client_query_call}}', ['query_id', 'line_id', 'info']);
    }

    public function down()
    {
        $this->dropTable('{{%client_query_call}}');
    }

}
