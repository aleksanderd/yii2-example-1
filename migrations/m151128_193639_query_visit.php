<?php

use dektrium\user\migrations\Migration;

class m151128_193639_query_visit extends Migration
{
    public function up()
    {
        $this->addColumn('{{%client_query}}', 'visit_id', $this->bigInteger() . ' AFTER `hit_id`');
        $this->addForeignKey('client_query_visit_rel', '{{%client_query}}', 'visit_id',
            '{{%client_visit}}', 'id', 'SET NULL', 'SET NULL');

        $sql = 'UPDATE {{%client_query}} SET visit_id = (SELECT visit_id FROM {{%widget_hit}} WHERE hit_id = id)';
        Yii::$app->db->createCommand($sql)->execute();
    }

    public function down()
    {
        $this->dropForeignKey('client_query_visit_rel', '{{%client_query}}');
        $this->dropColumn('{{%client_query}}', 'visit_id');
    }

}
