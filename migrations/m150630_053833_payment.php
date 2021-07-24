<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150630_053833_payment extends Migration
{
    public function up()
    {
        $this->createTable('{{%payment}}', [
            'id' => Schema::TYPE_BIGPK,
            'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'method' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
            'amount' => Schema::TYPE_MONEY . ' NOT NULL',
            'description' => Schema::TYPE_STRING . '(255) NOT NULL DEFAULT ""',
            'details_data' => Schema::TYPE_TEXT,
        ], $this->tableOptions);

        $this->addForeignKey('payment_user_rel', '{{%payment}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('payment_method', '{{%payment}}', 'method');
        $this->createIndex('payment_status_amount', '{{%payment}}', ['status', 'amount']);
        $this->createIndex('payment_at', '{{%payment}}', 'at');

        $this->createTable('{{%transaction}}', [
            'id' => Schema::TYPE_BIGPK,
            'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'payment_id' => Schema::TYPE_BIGINT,
            'query_id' => Schema::TYPE_BIGINT,
            'notification_id' => Schema::TYPE_BIGINT,
            'at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'amount' => Schema::TYPE_MONEY . ' NOT NULL',
            'description' => Schema::TYPE_STRING . '(255) NOT NULL DEFAULT ""',
            'details_data' => Schema::TYPE_TEXT,
        ], $this->tableOptions);

        $this->addForeignKey('transaction_user_rel', '{{%transaction}}', 'user_id',
            '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('transaction_payment_rel', '{{%transaction}}', 'payment_id',
            '{{%payment}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('transaction_query_rel', '{{%transaction}}', 'query_id',
            '{{%client_query}}', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('transaction_notification_rel', '{{%transaction}}', 'notification_id',
            '{{%notification}}', 'id', 'SET NULL', 'SET NULL');
        $this->createIndex('transaction_amount', '{{%transaction}}', 'amount');
        $this->createIndex('transaction_at', '{{%transaction}}', 'at');
    }

    public function down()
    {
        $this->dropTable('{{%transaction}}');
        $this->dropTable('{{%payment}}');
    }
    
}
