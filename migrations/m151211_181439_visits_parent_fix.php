<?php

use yii\db\Migration;

class m151211_181439_visits_parent_fix extends Migration
{
    public function up()
    {
        $this->dropForeignKey('cvisit_last_rel', '{{%client_visit}}');
        $this->addForeignKey('cvisit_previous_rel', '{{%client_visit}}', 'previous_id',
            '{{%client_visit}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        return false;
    }

}
