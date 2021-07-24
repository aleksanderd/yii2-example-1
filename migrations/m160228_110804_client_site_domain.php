<?php

use yii\db\Migration;

class m160228_110804_client_site_domain extends Migration
{
    public function up()
    {
        $this->addColumn('{{%client_site}}', 'domain', $this->string()->unique() . ' AFTER `url`');
    }

    public function down()
    {
        $this->dropColumn('{{%client_site}}', 'domain');
    }

}
