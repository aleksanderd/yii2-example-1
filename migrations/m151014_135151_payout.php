<?php

use dektrium\user\migrations\Migration;

class m151014_135151_payout extends Migration
{
    public function up()
    {
        $this->createTable('{{%payout}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'transaction_id' => $this->bigInteger()->unique(),
            'status' => $this->integer()->notNull()->defaultValue(1),
            'amount' => $this->decimal(19, 2)->notNull()->defaultValue(0),
            'comment' => $this->text(),
            'details_data' => $this->text(),
            'created_at' => $this->integer()->notNull()->defaultValue(0),
            'updated_at' => $this->integer()->notNull()->defaultValue(0),
        ], $this->tableOptions);
        $this->addForeignKey('payout_user_rel', '{{%payout}}', 'user_id', '{{%user}}', 'id');
        $this->addForeignKey('payout_transaction_rel', '{{%payout}}', 'transaction_id', '{{%transaction}}', 'id');
        $this->createIndex('payout_status_amount', '{{%payout}}', ['status', 'amount']);
    }

    public function down()
    {
        $this->dropTable('{{%payout}}');
    }

}
