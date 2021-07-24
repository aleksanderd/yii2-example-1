<?php

use dektrium\user\migrations\Migration;

class m150824_183949_tariff extends Migration
{
    public function up()
    {
        $this->createTable('{{%tariff}}', [
            'id' => $this->primaryKey(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'title' => $this->string(50)->notNull(),
            'desc' => $this->string()->notNull()->defaultValue(''),
            'desc_details' => $this->text(),
            'desc_internal' => $this->text(),

            'renewable' => $this->smallInteger()->notNull()->defaultValue(1),
            'price' => $this->money()->notNull(),
            'lifetime_measure' => $this->smallInteger()->notNull()->defaultValue(0),
            'lifetime' => $this->integer()->notNull()->defaultValue(0),
            'queries' => $this->integer()->notNull()->defaultValue(0),
            'minutes' => $this->integer()->notNull()->defaultValue(0),
            'messages' => $this->integer()->notNull()->defaultValue(0),
            'space' => $this->integer()->notNull()->defaultValue(0),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

        ], $this->tableOptions);
        $this->createIndex('tariff_status', '{{%tariff}}', 'status');
        $this->createIndex('tariff_title', '{{%tariff}}', 'title', true);
        $this->createIndex('tariff_lifetime', '{{%tariff}}', ['lifetime_measure', 'lifetime']);
        $this->createIndex('tariff_limits', '{{%tariff}}', ['queries', 'minutes', 'messages', 'space']);
        $this->createIndex('tariff_price', '{{%tariff}}', 'price');
        $this->createIndex('tariff_ts', '{{%tariff}}', ['created_at', 'updated_at']);

        $this->createTable('{{%user_tariff}}', [
            'id' => $this->bigPrimaryKey(),
            'user_id' => $this->integer()->notNull(),
            'tariff_id' => $this->integer(),

            'title' => $this->string(50)->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'renew' => $this->smallInteger()->defaultValue(0),
            'started_at' => $this->integer(),
            'finished_at' => $this->integer(),

            'renewable' => $this->smallInteger()->notNull()->defaultValue(0),
            'price' => $this->money()->notNull(),
            'lifetime_measure' => $this->smallInteger()->notNull()->defaultValue(1),
            'lifetime' => $this->integer()->notNull()->defaultValue(0),
            'queries' => $this->integer()->notNull()->defaultValue(0),
            'queries_used' => $this->integer()->notNull()->defaultValue(0),
            'seconds' => $this->integer()->notNull()->defaultValue(0),
            'seconds_used' => $this->integer()->notNull()->defaultValue(0),
            'messages' => $this->integer()->notNull()->defaultValue(0),
            'messages_used' => $this->integer()->notNull()->defaultValue(0),
            'space' => $this->integer()->notNull()->defaultValue(0),
            'space_used' => $this->integer()->notNull()->defaultValue(0),

        ], $this->tableOptions);
        $this->addForeignKey('user_tariff_user_rel', '{{%user_tariff}}', 'user_id',
            '{{%user}}', 'id', 'RESTRICT', 'RESTRICT');
        $this->addForeignKey('user_tariff_tariff_rel', '{{%user_tariff}}', 'tariff_id',
            '{{%tariff}}', 'id', 'SET NULL', 'SET NULL');
        $this->createIndex('user_tariff_title', '{{%user_tariff}}', ['title']);
        $this->createIndex('user_tariff_status_renew', '{{%user_tariff}}', ['status', 'renew']);
        $this->createIndex('user_tariff_ts', '{{%user_tariff}}', ['started_at', 'finished_at']);
        $this->createIndex('user_tariff_lifetime', '{{%tariff}}', ['lifetime_measure', 'lifetime']);
        $this->createIndex('user_tariff_limits', '{{%user_tariff}}', ['renewable', 'seconds', 'messages', 'queries', 'space']);
        $this->createIndex('user_tariff_limits_used', '{{%user_tariff}}',
            ['queries_used', 'seconds_used', 'messages_used', 'space_used']);
        $this->createIndex('user_tariff_price', '{{%tariff}}', 'price');

        $this->addColumn('{{%transaction}}', 'user_tariff_id', $this->bigInteger() . ' AFTER notification_id');
        $this->addForeignKey('transaction_user_tariff_rel', '{{%transaction}}', 'user_tariff_id',
            '{{%user_tariff}}', 'id', 'RESTRICT', 'RESTRICT');
        $this->addColumn('{{%client_query}}', 'user_tariff_id', $this->bigInteger() . ' AFTER rule_id');
        $this->addForeignKey('client_query_user_tariff_rel', '{{%client_query}}', 'user_tariff_id',
            '{{%user_tariff}}', 'id', 'RESTRICT', 'RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('client_query_user_tariff_rel', '{{%client_query}}');
        $this->dropColumn('{{%client_query}}', 'user_tariff_id');
        $this->dropForeignKey('transaction_user_tariff_rel', '{{%transaction}}');
        $this->dropColumn('{{%transaction}}', 'user_tariff_id');
        $this->dropTable('{{%user_tariff}}');
        $this->dropTable('{{%tariff}}');
    }

}
