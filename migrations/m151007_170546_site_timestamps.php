<?php

use app\models\ClientSite;
use dektrium\user\migrations\Migration;

class m151007_170546_site_timestamps extends Migration
{
    public function up()
    {
        $this->addColumn('{{%client_site}}', 'created_at', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('{{%client_site}}', 'updated_at', $this->integer()->notNull()->defaultValue(0));
        /** @var ClientSite[] $sites */
        $sites = ClientSite::find()->all();
        foreach ($sites as $site) {
            $site->created_at = $site->updated_at = $site->user->created_at;
            $site->save(false, ['created_at', 'updated_at']);
        }
        $this->createIndex('client_site_timestamps', '{{%client_site}}', ['created_at', 'updated_at']);
    }

    public function down()
    {
        $this->dropColumn('{{%client_site}}', 'updated_at');
        $this->dropColumn('{{%client_site}}', 'created_at');
    }

}
