<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;
use flyiing\translation\models\TSourceMessage;
use flyiing\translation\models\TMessage;

class m150817_155543_translation extends Migration
{

    public function up()
    {
        $tblSourceMessage = TSourceMessage::tableName();
        $tblMessage = TMessage::tableName();
        $this->createTable($tblSourceMessage, [
            'id' => $this->primaryKey(),
            'category' => $this->string(32),
            'message' => $this->text(),
        ], $this->tableOptions);

        $this->createIndex('t_source_message_cat_msg', $tblSourceMessage, ['category', 'message(32)']);

        $this->createTable($tblMessage, [
            'id' => $this->integer(),
            'language' => $this->string(16),
            'translation' => $this->text(),
        ], $this->tableOptions);

        $this->addPrimaryKey('t_message_primary', $tblMessage, ['id', 'language']);
        $this->addForeignKey('t_message_id_rel', $tblMessage, 'id',
            $tblSourceMessage, 'id', 'CASCADE', 'RESTRICT');
        $this->createIndex('t_message_translation', $tblMessage, 'translation(32)');

    }

    public function down()
    {
        $this->dropTable(TMessage::tableName());
        $this->dropTable(TSourceMessage::tableName());
    }

}
