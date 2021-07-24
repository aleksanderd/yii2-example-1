<?php

use yii\db\Schema;
use yii\db\Migration;

class m150526_075413_profile_fields extends Migration
{
    public function up()
    {
        $this->addColumn('{{%profile}}', 'company', Schema::TYPE_STRING . '(255)');
        $this->addColumn('{{%profile}}', 'phone', Schema::TYPE_STRING . '(12)');
    }

    public function down()
    {
        $this->dropColumn('{{%profile}}', 'company');
        $this->dropColumn('{{%profile}}', 'phone');
    }
    
}
