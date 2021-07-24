<?php

use yii\db\Schema;
use yii\db\Migration;

class m150729_205213_drop_default_cc extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%client_site}}', 'allowed_cc_data');
        $this->dropColumn('{{%client_site}}', 'default_cc');
    }

    public function down()
    {
        $this->addColumn('{{%client_site}}', 'allowed_cc_data', Schema::TYPE_TEXT);
        $this->addColumn('{{%client_site}}', 'default_cc', Schema::TYPE_STRING . '(11)');
    }
    
}
