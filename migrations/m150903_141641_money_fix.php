<?php

use yii\db\Schema;
use yii\db\Migration;

class m150903_141641_money_fix extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%transaction}}', 'amount', $this->decimal(19, 2));
        $this->alterColumn('{{%payment}}', 'amount', $this->decimal(19, 2));
        $this->alterColumn('{{%client_query}}', 'client_cost', $this->decimal(19, 2));
        $this->alterColumn('{{%client_query_call}}', 'client_cost', $this->decimal(19, 2));
        $this->alterColumn('{{%client_query_call}}', 'client_price', $this->decimal(19, 2));
        $this->alterColumn('{{%tariff}}', 'price', $this->decimal(19, 2));
        $this->alterColumn('{{%user_tariff}}', 'price', $this->decimal(19, 2));
    }

    public function down()
    {
    }

}
