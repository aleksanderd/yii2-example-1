<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;

class m150826_111555_black_call_info extends Migration
{
    public function up()
    {
        $this->createTable('{{%black_call_info}}', [
            'id' => $this->bigPrimaryKey(),
            'user_id' => $this->integer(),
            'call_info' => $this->string()->notNull(),
            'comment' => $this->text(),
        ], $this->tableOptions);
        $this->addForeignKey('black_call_info_user_rel', '{{%black_call_info}}', 'user_id',
            '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('black_call_info_uci', '{{%black_call_info}}', ['user_id', 'call_info'], true);
    }

    public function down()
    {
        $this->dropTable('{{%black_call_info}}');
    }

}
