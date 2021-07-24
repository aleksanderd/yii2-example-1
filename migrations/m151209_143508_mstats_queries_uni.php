<?php

use dektrium\user\migrations\Migration;

class m151209_143508_mstats_queries_uni extends Migration
{
    public function up()
    {
        $this->addColumn('{{%modal_text_stat}}', 'queries_uni', $this->integer()->notNull()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn('{{%modal_text_stat}}', 'queries_uni');
    }

}
