<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150719_225005_promocode extends Migration
{
    public function up()
    {
        $this->createTable('{{%promocode}}', [
            'id' => Schema::TYPE_BIGPK,
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'expires_at' => Schema::TYPE_INTEGER . ' NULL DEFAULT NULL',
            'code' => Schema::TYPE_STRING . '(255) NOT NULL',
            'amount' => Schema::TYPE_MONEY . ' NOT NULL',
            'count' => Schema::TYPE_INTEGER . ' NULL DEFAULT NULL',
            'user_id' => Schema::TYPE_INTEGER . ' NULL DEFAULT NULL',
            'description' => Schema::TYPE_TEXT . ' NULL DEFAULT NULL',
            'new_only' => Schema::TYPE_SMALLINT . ' NULL DEFAULT NULL',
        ], $this->tableOptions);

        $this->createIndex('promocode_created_at', '{{%promocode}}', 'created_at');
        $this->createIndex('promocode_expires_at', '{{%promocode}}', 'expires_at');
        $this->createIndex('promocode_code', '{{%promocode}}', 'code', true);
        $this->createIndex('promocode_amount', '{{%promocode}}', 'amount');
        $this->createIndex('promocode_count', '{{%promocode}}', 'count');
//        $this->createIndex('promocode_user_id', '{{%promocode}}', 'user_id');
        $this->createIndex('promocode_new_only', '{{%promocode}}', 'new_only');

        $this->addForeignKey('promocode_user_rel', '{{%promocode}}', 'user_id',
            '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->addColumn('{{%payment}}', 'promocode_id', Schema::TYPE_BIGINT . ' NULL DEFAULT NULL');
        $this->createIndex('promocode_id', '{{%payment}}', 'promocode_id');
        $this->addForeignKey('payment_promocode_rel', '{{%payment}}', 'promocode_id',
            '{{%promocode}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('payment_promocode_rel', '{{%payment}}');
        $this->dropColumn('{{%payment}}', 'promocode_id');
        $this->dropTable('{{%promocode}}');
    }
}
