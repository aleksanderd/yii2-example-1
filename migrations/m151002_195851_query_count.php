<?php

use dektrium\user\migrations\Migration;

class m151002_195851_query_count extends Migration
{
    public function up()
    {
        $this->addColumn('{{%client_query}}', 'call_info_count', $this->integer()->defaultValue(0) . ' AFTER `call_info`');
        $this->createIndex('client_query_call_info_count', '{{%client_query}}', 'call_info_count');
        $this->addColumn('{{%conversion}}', 'queries_unique', $this->integer()->defaultValue(0) . ' AFTER `queries`');
    }

    public function down()
    {
        $this->dropColumn('{{%conversion}}', 'queries_unique');
        $this->dropColumn('{{%client_query}}', 'call_info_count');
    }

}

