<?php

use dektrium\user\migrations\Migration;

class m150906_155315_conversion extends Migration
{
    public function up()
    {
        $this->createTable('{{%conversion}}', [
            'user_id' => $this->integer()->notNull(),
            'site_id' => $this->bigInteger()->notNull(),
//            'page_id' => $this->bigInteger(),
            'datetime' => $this->integer()->notNull(),
            'period' => $this->integer()->notNull()->defaultValue(86400),

            'hits' => $this->integer()->notNull()->defaultValue(0),
            'visits' => $this->integer()->notNull()->defaultValue(0),
            'visits_unique' => $this->integer()->notNull()->defaultValue(0),
            'queries' => $this->integer()->notNull()->defaultValue(0),
            'queries_unpaid' => $this->integer()->notNull()->defaultValue(0),
            'queries_success' => $this->integer()->notNull()->defaultValue(0),
            'queries_calls' => $this->integer()->notNull()->defaultValue(0),
            'record_time' => $this->integer()->notNull()->defaultValue(0),
            'client_cost' => $this->decimal(19, 2)->notNull()->defaultValue(0),
            'cost' => $this->decimal(19, 4)->notNull()->defaultValue(0),
        ], $this->tableOptions);

        $this->addForeignKey('conversion_user_rel', '{{%conversion}}', 'user_id',
            '{{%user}}', 'id', 'RESTRICT', 'RESTRICT');
        $this->addForeignKey('conversion_site_rel', '{{%conversion}}', 'site_id',
            '{{%client_site}}', 'id', 'RESTRICT', 'RESTRICT');
//        $this->addForeignKey('conversion_page_rel', '{{%conversion}}', 'page_id',
//            '{{%client_page}}', 'id', 'RESTRICT', 'RESTRICT');
        $this->addPrimaryKey('conversion_pk', '{{%conversion}}', ['user_id', 'site_id', 'period', 'datetime']);
//        $this->createIndex('conversion_datetime', '{{%conversion}}', 'datetime');
    }

    public function down()
    {
        $this->dropTable('{{%conversion}}');
    }

}
