<?php

use yii\db\Migration;

class m151228_230828_pt_admin extends Migration
{
    public function up()
    {
        $this->addColumn('{{%payment}}', 'admin_id', $this->integer());
        $this->addColumn('{{%transaction}}', 'admin_id', $this->integer());
        $this->addForeignKey('payment_admin_rel', '{{%payment}}', 'admin_id', '{{%user}}', 'id');
        $this->addForeignKey('transaction_admin_rel', '{{%transaction}}', 'admin_id', '{{%user}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('transaction_admin_rel', '{{%transaction}}');
        $this->dropForeignKey('payment_admin_rel', '{{%payment}}');
        $this->dropColumn('{{%transaction}}', 'admin_id');
        $this->dropColumn('{{%payment}}', 'admin_id');
    }

}
