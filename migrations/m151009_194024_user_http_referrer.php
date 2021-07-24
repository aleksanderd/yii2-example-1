<?php

use dektrium\user\migrations\Migration;

class m151009_194024_user_http_referrer extends Migration
{
    public function up()
    {
        $this->addColumn('{{%user}}', 'http_referrer', $this->string());
        $this->createIndex('user_http_referrer', '{{%user}}', 'http_referrer');
    }

    public function down()
    {
        $this->dropColumn('{{%user}}', 'http_referrer');
    }

}
