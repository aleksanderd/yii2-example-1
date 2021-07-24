<?php

use dektrium\user\migrations\Migration;

class m151004_125155_refsys extends Migration
{
    public function up()
    {
        $this->createTable('{{%referral_url}}', [
            'id' => $this->primaryKey(),
            'status' => $this->smallInteger()->notNull()->defaultValue(100),
            'user_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'promocode_id' => $this->bigInteger(),
            'created_at' => $this->integer(),
        ], $this->tableOptions);
        $this->addForeignKey('referral_url_user_rel', '{{%referral_url}}', 'user_id', '{{%user}}', 'id');
        $this->addForeignKey('referral_url_promocode_rel', '{{%referral_url}}', 'promocode_id', '{{%promocode}}', 'id');
        $this->createIndex('referral_url_status_user_title', '{{%referral_url}}', ['status', 'user_id', 'title'], true);

        $this->createTable('{{referral_stats}}', [
            'user_id' => $this->integer()->notNull(),
            'datetime' => $this->integer()->notNull(),
            'period' => $this->integer()->notNull()->defaultValue(86400),
            'url_id' => $this->integer(),
            'visits' => $this->integer()->notNull()->defaultValue(0),
            'visits_unique' => $this->integer()->notNull()->defaultValue(0),
            'registered' => $this->integer()->notNull()->defaultValue(0),
            'active' => $this->integer()->notNull()->defaultValue(0),
            'paid' => $this->decimal(19, 2)->notNull()->defaultValue(0),
        ], $this->tableOptions);
        $this->addPrimaryKey('referral_stats_pk', '{{%referral_stats}}', ['user_id', 'datetime', 'url_id']);
        $this->addForeignKey('referral_stats_user_rel', '{{%referral_stats}}', 'user_id', '{{%user}}', 'id');
        $this->addForeignKey('referral_stats_url_rel', '{{%referral_stats}}', 'url_id', '{{%referral_url}}', 'id');

        $this->createTable('{{%user_referral}}', [
            'partner_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull()->unique(),
            'url_id' => $this->integer(),
            'scheme' => $this->smallInteger()->notNull()->defaultValue(0),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'paid' => $this->decimal(19, 2)->notNull()->defaultValue(0),
        ], $this->tableOptions);
        $this->addForeignKey('user_referral_partner_rel', '{{%user_referral}}', 'partner_id', '{{%user}}', 'id');
        $this->addForeignKey('user_referral_user_rel', '{{%user_referral}}', 'user_id', '{{%user}}', 'id');
        $this->addForeignKey('user_referral_url_rel', '{{%user_referral}}', 'url_id', '{{%referral_url}}', 'id');
        $this->createIndex('user_referral_status_paid', '{{%user_referral}}', ['status', 'paid']);

        $this->addPrimaryKey('user_referral_pk', '{{%user_referral}}', ['partner_id', 'user_id']);

        $this->createTable('{{%user_referral_transaction}}', [
            'partner_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'transaction_id' => $this->bigInteger()->notNull()->unique(),
            'payment_id' => $this->bigInteger()->notNull()->unique(),
            'at' => $this->integer()->notNull(),
        ], $this->tableOptions);
        $this->addForeignKey('user_referral_transaction_partner_rel', '{{%user_referral_transaction}}', 'partner_id',
            '{{%user}}', 'id');
        $this->addForeignKey('user_referral_transaction_user_rel', '{{%user_referral_transaction}}', 'user_id',
            '{{%user}}', 'id');
        $this->addForeignKey('user_referral_transaction_transaction_rel', '{{%user_referral_transaction}}', 'transaction_id',
            '{{%transaction}}', 'id');
        $this->addForeignKey('user_referral_transaction_payment_rel', '{{%user_referral_transaction}}', 'payment_id',
            '{{%payment}}', 'id');
        $this->createIndex('user_referral_transaction_at', '{{%user_referral_transaction}}', 'at');

        $this->addPrimaryKey('user_referral_transaction_pk', '{{%user_referral_transaction}}',
            ['partner_id', 'user_id', 'transaction_id']);
    }

    public function down()
    {
        $this->dropTable('{{%user_referral_transaction}}');
        $this->dropTable('{{%user_referral}}');
        $this->dropTable('{{%referral_stats}}');
        $this->dropTable('{{%referral_url}}');
    }

}
