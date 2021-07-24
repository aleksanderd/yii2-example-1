<?php

use dektrium\user\migrations\Migration;

class m151201_124352_wchecks extends Migration
{
    public function up()
    {
        $this->addColumn('{{%client_site}}', 'w_checked_at', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('{{%client_site}}', 'w_changed_at', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('{{%client_site}}', 'w_check_result', $this->integer()->notNull()->defaultValue(-1));
    }

    public function down()
    {
        $this->dropColumn('{{%client_site}}', 'w_check_result');
        $this->dropColumn('{{%client_site}}', 'w_changed_at');
        $this->dropColumn('{{%client_site}}', 'w_checked_at');
    }

}
