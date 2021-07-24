<?php

use dektrium\user\migrations\Migration;

class m150922_134720_rule_tz_act extends Migration
{
    public function up()
    {
        $this->addColumn('{{%client_rule}}', 'active', $this->smallInteger()->defaultValue(1) . ' AFTER `page_id`');
        $this->createIndex('client_rule_active', '{{%client_rule}}', 'active');
        $this->addColumn('{{%client_rule}}', 'timezone', $this->string(255)->defaultValue('') . ' AFTER `description`');
        $this->createIndex('client_rule_timezone', '{{%client_rule}}', 'timezone');
    }

    public function down()
    {
        $this->dropColumn('{{%client_rule}}', 'timezone');
        $this->dropColumn('{{%client_rule}}', 'active');
    }

}
