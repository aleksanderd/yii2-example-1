<?php

use dektrium\user\migrations\Migration;

class m151128_124204_query_stats extends Migration
{
    public function up()
    {
        $this->addColumn('{{%client_query}}', 'visit_time', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('{{%client_query}}', 'hit_time', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('{{%client_query}}', 'trigger', $this->smallInteger()->notNull()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn('{{%client_query}}', 'trigger');
        $this->dropColumn('{{%client_query}}', 'hit_time');
        $this->dropColumn('{{%client_query}}', 'visit_time');
    }

}
