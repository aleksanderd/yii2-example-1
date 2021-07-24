<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150707_154148_call_cost extends Migration
{
    public function up()
    {
        $this->addColumn('{{%client_query}}', 'client_cost', Schema::TYPE_DECIMAL . '(10,4) UNSIGNED');
        $this->createIndex('client_query_cost', '{{%client_query}}', 'client_cost');

        $this->addColumn('{{%client_query_call}}', 'client_price', Schema::TYPE_DECIMAL . '(10,4) UNSIGNED');
        $this->addColumn('{{%client_query_call}}', 'client_cost', Schema::TYPE_DECIMAL . '(10,4) UNSIGNED');
        $this->createIndex('cq_call_price', '{{%client_query_call}}', 'client_price');
        $this->createIndex('cq_call_cost', '{{%client_query_call}}', 'client_cost');
    }

    public function down()
    {
        $this->dropColumn('{{%client_query_call}}', 'client_cost');
        $this->dropColumn('{{%client_query_call}}', 'client_price');
        $this->dropColumn('{{%client_query}}', 'client_cost');
    }

}
