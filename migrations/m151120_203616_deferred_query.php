<?php

use yii\db\Schema;
use yii\db\Migration;

class m151120_203616_deferred_query extends Migration
{
    public function up()
    {
        $this->addColumn('{{%client_query}}', 'deferred_id', $this->bigInteger());
        $this->addForeignKey('client_query_deferred_ref', '{{%client_query}}', 'deferred_id', '{{%client_query}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('client_query_deferred_ref', '{{%client_query}}');
        $this->dropColumn('{{%client_query}}', 'deferred_id');
    }

}
