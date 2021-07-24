<?php

use yii\db\Migration;

class m151216_185543_client_query_rel_fix extends Migration
{
    public function up()
    {
        $this->dropForeignKey('client_query_user_rel', '{{%client_query}}');
        $this->dropForeignKey('client_query_site_rel', '{{%client_query}}');
        $this->dropForeignKey('client_query_rule_rel', '{{%client_query}}');

        $this->addForeignKey('client_query_user_rel', '{{%client_query}}', 'user_id',
            '{{%user}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('client_query_site_rel', '{{%client_query}}', 'site_id',
            '{{%client_site}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('client_query_rule_rel', '{{%client_query}}', 'rule_id',
            '{{%client_rule}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function down()
    {
    }

}
