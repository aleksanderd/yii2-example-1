<?php

use dektrium\user\migrations\Migration;

class m151008_102026_promocode_fix extends Migration
{
    public function up()
    {
        $this->createTable('{{%promocode_activation}}', [
            'at' => $this->integer()->notNull(),
            'partner_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'promocode_id' => $this->bigInteger()->notNull(),
            'partner_transaction_id' => $this->bigInteger(),
            'user_transaction_id' => $this->bigInteger(),
        ], $this->tableOptions);

        $this->addForeignKey('promocode_activation_partner_rel', '{{%promocode_activation}}', 'partner_id', '{{%user}}', 'id');
        $this->addForeignKey('promocode_activation_user_rel', '{{%promocode_activation}}', 'user_id', '{{%user}}', 'id');
        $this->addForeignKey('promocode_activation_promocode_rel', '{{%promocode_activation}}', 'promocode_id', '{{%promocode}}', 'id');
        $this->addForeignKey('promocode_activation_partner_transaction_rel', '{{%promocode_activation}}', 'partner_transaction_id', '{{%transaction}}', 'id');
        $this->addForeignKey('promocode_activation_user_transaction_rel', '{{%promocode_activation}}', 'user_transaction_id', '{{%transaction}}', 'id');
        $this->addPrimaryKey('promocode_activation_pk', '{{%promocode_activation}}', ['partner_id', 'user_id', 'promocode_id']);
        $this->createIndex('promocode_activation_at', '{{%promocode_activation}}', 'at');
    }

    public function down()
    {
        $this->dropTable('{{%promocode_activation}}');
    }

}
