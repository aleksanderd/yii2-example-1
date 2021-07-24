<?php

use yii\db\Migration;

class m151023_164208_refsys_paccess extends Migration
{
    public function up()
    {
        $this->addColumn('{{%user_referral}}', 'p_access', $this->smallInteger()->notNull()->defaultValue(100));
        $this->createIndex('user_referral_p_access', '{{%user_referral}}', 'p_access');
    }

    public function down()
    {
        $this->dropColumn('{{%user_referral}}', 'p_access');
    }

}
