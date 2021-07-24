<?php

use dektrium\user\migrations\Migration;
use flyiing\translation\models\TSourceMessage;

class m151124_010101_collate extends Migration
{

    public function up()
    {
        $tblSourceMessage = TSourceMessage::tableName();
        $this->alterColumn($tblSourceMessage, 'message', $this->text() . ' COLLATE utf8_bin');
    }

    public function down()
    {
    }

}
